<?php

namespace App\Services\Analytics;

use App\Models\BlogPost;
use App\Models\ClickEvent;
use App\Models\MarketingGoal;
use App\Models\Order;
use App\Models\PageView;
use App\Models\User;
use App\Support\Markets;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Compare les objectifs mensuels (MarketingGoal) aux résultats réels, projette la fin du mois,
 * note la stratégie sur 100 et propose des actions concrètes.
 */
class GoalTracker
{
    /** @var array<string, float> */
    private array $cache = [];

    /** Statuts qui ne comptent pas dans la note (pas encore jugeables ou données absentes). */
    private const NOT_SCORED = ['À venir', 'Trop tôt', 'Indisponible'];

    public function __construct(private PlatformStats $platform) {}

    // ───────────────────────── Réalisé ─────────────────────────

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} bornes UTC du mois (fuseau de Tunis) */
    public function monthRange(string $month): array
    {
        $start = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $month.'-01 00:00:00', config('marketing.timezone'));

        return [$start->utc(), $start->endOfMonth()->utc()];
    }

    /** Part du mois écoulée (1 = mois terminé), pour projeter le résultat de fin de mois. */
    public function elapsed(string $month): float
    {
        [$from, $to] = $this->monthRange($month);
        $now = CarbonImmutable::now();

        if ($now->gte($to)) {
            return 1.0;
        }
        if ($now->lte($from)) {
            return 0.0;
        }

        return max(0.02, ($now->getTimestamp() - $from->getTimestamp()) / ($to->getTimestamp() - $from->getTimestamp()));
    }

    public function actual(string $metric, string $month, ?string $country = null): float
    {
        $key = "$metric|$month|$country";

        return $this->cache[$key] ??= $this->compute($metric, $month, $country);
    }

    private function compute(string $metric, string $month, ?string $country): float
    {
        [$from, $to] = $this->monthRange($month);

        $views = fn () => PageView::query()
            ->whereBetween('created_at', [$from, $to])
            ->when($country, fn ($q) => $q->where('country', $country));

        // Utilisateurs « de ce pays » = ayant navigué depuis ce pays.
        $userFilter = function (Builder $q, string $column = 'id') use ($country) {
            if ($country) {
                $q->whereIn($column, PageView::query()->select('user_id')->whereNotNull('user_id')->where('country', $country));
            }
        };

        return (float) match ($metric) {
            'visitors' => $views()->distinct()->count('visitor_hash'),
            'google_visitors' => $views()->where('source', 'Google')->distinct()->count('visitor_hash'),
            'blog_readers' => $views()->where('route_name', 'blog.show')->distinct()->count('visitor_hash'),
            'signups' => User::query()->whereBetween('created_at', [$from, $to])->tap(fn ($q) => $userFilter($q))->count(),
            'conversion' => ($v = $this->actual('visitors', $month, $country)) > 0
                ? round($this->actual('signups', $month, $country) / $v * 100, 1) : 0,
            'orders' => Order::query()->whereBetween('created_at', [$from, $to])->tap(fn ($q) => $userFilter($q, 'user_id'))->count(),
            'paid' => Order::query()->whereBetween('created_at', [$from, $to])->where('status', 'paid')
                ->tap(fn ($q) => $userFilter($q, 'user_id'))->count(),
            // Lien vers notre numéro (wa.me/216…), pas les liens « partager sur WhatsApp » (wa.me/?text=).
            'whatsapp' => ClickEvent::query()->whereBetween('created_at', [$from, $to])
                ->where('target', 'like', 'wa.me/_%')->distinct()->count('visitor_hash'),
            // Plateforme d'enseignement (0 si l'API ne répond pas : la ligne est alors marquée « Indisponible »).
            'active_students' => $this->platform->value($month, 'active_students', $country) ?? 0,
            'retention' => $this->platform->value($month, 'retention_rate') ?? 0,
            'attendance' => $this->platform->value($month, 'attendance_rate') ?? 0,
            'articles' => BlogPost::query()->where('status', 'published')
                ->whereBetween('published_at', [$from, min($to, CarbonImmutable::now())])->count(),
            default => 0,
        };
    }

    // ───────────────────────── Suivi des objectifs ─────────────────────────

    /**
     * @return Collection<int, object> une ligne par objectif avec réalisé, projection, % et statut
     */
    public function progress(string $month): Collection
    {
        $elapsed = $this->elapsed($month);

        return MarketingGoal::where('month', $month)->get()
            ->sortBy(fn (MarketingGoal $g) => [array_search($g->metric, array_keys(MarketingGoal::METRICS)), $g->country ?? ''])
            ->values()
            ->map(function (MarketingGoal $goal) use ($month, $elapsed) {
                $actual = $this->actual($goal->metric, $month, $goal->country);
                // Un taux ne se projette pas ; un volume se projette au prorata du mois écoulé.
                // (un nombre d'élèves distincts ne se projette pas non plus : un élève hebdomadaire compte dès la 1re semaine).
                $noProjection = $goal->isRate() || ! (MarketingGoal::METRICS[$goal->metric]['project'] ?? true);
                $projected = $noProjection || $elapsed >= 1 ? $actual : ($elapsed > 0 ? $actual / $elapsed : 0);
                $ratio = $goal->target > 0 ? $projected / $goal->target : 0;

                $unavailable = $goal->isPlatform() && $this->platform->month($month) === null;

                [$status, $color] = match (true) {
                    $elapsed <= 0 => ['À venir', 'gray'],
                    $unavailable => ['Indisponible', 'gray'],
                    // Les premiers jours, une projection n'a pas de sens : on ne juge pas encore.
                    $elapsed < 0.15 && $ratio < 1 => ['Trop tôt', 'gray'],
                    $ratio >= 1 => [$elapsed >= 1 ? 'Atteint' : 'En avance', 'success'],
                    $ratio >= 0.8 => [$elapsed >= 1 ? 'Presque atteint' : 'Dans les temps', 'warning'],
                    default => [$elapsed >= 1 ? 'Non atteint' : 'En retard', 'danger'],
                };

                return (object) [
                    'goal' => $goal,
                    'label' => $goal->metricLabel(),
                    'country' => $goal->country,
                    'scope' => $goal->country ? Markets::name($goal->country, 'fr') : 'Tous pays',
                    'target' => $goal->target,
                    'actual' => $actual,
                    'projected' => round($projected, $goal->isRate() ? 1 : 0),
                    'percent' => $goal->target > 0 ? (int) round($actual / $goal->target * 100) : 0,
                    'ratio' => $ratio,
                    'status' => $status,
                    'color' => $color,
                    'unit' => $goal->unit(),
                ];
            });
    }

    /**
     * Note de la stratégie sur 100 : moyenne des taux d'atteinte (projetés), plafonnés à 100 %.
     *
     * @return array{score: ?int, verdict: string, color: string, summary: string}
     */
    public function score(string $month): array
    {
        $all = $this->progress($month);
        $rows = $all->filter(fn ($r) => ! in_array($r->status, self::NOT_SCORED, true));

        if ($rows->isEmpty()) {
            return $all->isEmpty()
                ? ['score' => null, 'verdict' => 'Aucun objectif évalué', 'color' => 'gray',
                    'summary' => 'Fixez des objectifs pour ce mois dans « Objectifs marketing » pour obtenir une note.']
                : ['score' => null, 'verdict' => 'Trop tôt pour noter', 'color' => 'gray',
                    'summary' => 'La note apparaît après les premiers jours du mois, quand la tendance devient fiable.'];
        }

        $score = (int) round($rows->avg(fn ($r) => min(1, $r->ratio)) * 100);
        $reached = $rows->where('ratio', '>=', 1)->count();

        [$verdict, $color] = match (true) {
            $score >= 90 => ['Stratégie efficace : on accélère', 'success'],
            $score >= 70 => ['Sur la bonne voie : quelques ajustements', 'info'],
            $score >= 50 => ['Résultats mitigés : il faut corriger le tir', 'warning'],
            default => ['Stratégie à revoir', 'danger'],
        };

        return [
            'score' => $score,
            'verdict' => $verdict,
            'color' => $color,
            'summary' => "{$reached} objectif(s) sur {$rows->count()} atteint(s) ou en avance".
                ($this->elapsed($month) < 1 ? ' (projection de fin de mois).' : '.'),
        ];
    }

    /** Historique des notes des derniers mois ayant des objectifs. */
    public function history(int $months = 6): Collection
    {
        return MarketingGoal::query()->select('month')->distinct()->orderByDesc('month')->limit($months)->pluck('month')
            ->map(fn ($month) => (object) (['month' => $month,
                'label' => ucfirst(CarbonImmutable::createFromFormat('Y-m-d', $month.'-01')->locale('fr')->isoFormat('MMMM YYYY')),
            ] + $this->score($month)));
    }

    // ───────────────────────── Recommandations ─────────────────────────

    /** @return array<int, array{level: string, text: string}> */
    public function recommendations(string $month): array
    {
        $rows = $this->progress($month)->filter(fn ($r) => ! in_array($r->status, self::NOT_SCORED, true));
        $tips = [];

        $late = fn (string $metric) => $rows->first(fn ($r) => $r->goal->metric === $metric && ! $r->country && $r->ratio < 0.8);

        // Plateforme d'enseignement : indépendant des visites du site.
        if ($r = $late('retention')) {
            $tips[] = ['level' => 'danger', 'text' => "Fidélisation à {$r->actual} % (objectif ".(int) $r->target.' %) : des élèves arrêtent après un mois. Appelez les parents des élèves absents ce mois-ci, envoyez un bilan de progression et proposez un créneau de rattrapage.'];
        }

        if ($r = $late('attendance')) {
            $tips[] = ['level' => 'warning', 'text' => "Assiduité à {$r->actual} % : envoyez un rappel WhatsApp la veille des séances et vérifiez que les horaires conviennent au fuseau de chaque pays."];
        }

        foreach ($rows->filter(fn ($r) => $r->country && $r->goal->metric === 'active_students' && $r->ratio < 0.8) as $r) {
            $tips[] = ['level' => 'warning', 'text' => "{$r->scope} : {$r->actual} élève(s) actif(s) sur la plateforme pour ".(int) $r->target.' visé(s). Relancez les inscrits de ce pays qui n’ont pas encore commencé (premier cours offert).'];
        }

        if ($this->progress($month)->contains('status', 'Indisponible')) {
            $tips[] = ['level' => 'info', 'text' => 'Les statistiques de la plateforme sont indisponibles : vérifiez PLATFORM_URL et PLATFORM_STATS_TOKEN (site) ainsi que MARKETING_API_TOKEN (plateforme), qui doivent être identiques.'];
        }

        if (PageView::count() === 0) {
            return [['level' => 'info', 'text' => 'Pas encore de visites enregistrées : vérifiez que le site est en production, que le sitemap est envoyé dans Google Search Console, puis revenez dans quelques jours.'], ...$tips];
        }


        // Marchés prioritaires en retard.
        foreach ($rows->filter(fn ($r) => $r->country && $r->goal->metric === 'visitors' && $r->ratio < 0.8) as $r) {
            $name = Markets::name($r->country, 'fr');
            $tips[] = ['level' => 'danger', 'text' => "{$r->scope} : {$r->actual} visiteurs pour un objectif de ".(int) $r->target.". Publiez un article ciblé {$name} (programme, villes, horaires du pays), cochez ce pays sur l’article et partagez-le dans les groupes de parents de ce pays."];
        }

        if ($late('google_visitors')) {
            $tips[] = ['level' => 'warning', 'text' => 'Le trafic Google est en retard : vérifiez dans Search Console que le sitemap est lu sans erreur, gardez le rythme d’un article par semaine et ajoutez des liens entre articles et pages régimes.'];
        }

        $visitors = $rows->first(fn ($r) => $r->goal->metric === 'visitors' && ! $r->country);
        $conversion = $late('conversion') ?? $late('signups');
        if ($conversion && $visitors && $visitors->ratio >= 0.8) {
            $tips[] = ['level' => 'warning', 'text' => 'Les visiteurs viennent mais s’inscrivent peu : renforcez les pages régimes (témoignages, premier cours d’essai, bouton WhatsApp) et regardez dans « Éléments les plus cliqués » ce qui intéresse vraiment.'];
        }

        if ($late('whatsapp') && $visitors && $visitors->ratio >= 0.8) {
            $tips[] = ['level' => 'warning', 'text' => 'Peu de parents écrivent sur WhatsApp malgré le trafic : terminez chaque article et chaque page régime par une invitation claire (« Posez-nous vos questions sur WhatsApp ») et répondez en moins d’une heure.'];
        }

        if ($late('blog_readers')) {
            $tips[] = ['level' => 'warning', 'text' => 'Peu de lecteurs sur le blog : partagez chaque article sur Facebook et WhatsApp aux heures d’affluence (voir le graphique des heures) et épinglez-le sur la page Facebook.'];
        }

        if ($r = $late('articles')) {
            $tips[] = ['level' => 'warning', 'text' => "Rythme éditorial insuffisant : {$r->actual} article(s) publié(s) pour ".(int) $r->target.' prévu(s). Programmez les articles à l’avance dans le back-office.'];
        }

        if (($paid = $late('paid')) && ($orders = $rows->first(fn ($r) => $r->goal->metric === 'orders' && ! $r->country)) && $orders->ratio >= 0.8) {
            $tips[] = ['level' => 'warning', 'text' => 'Beaucoup de demandes de commande mais peu sont payées : relancez les commandes en attente rapidement (dans les 24 h) par WhatsApp ou e-mail.'];
        }

        // Part des marchés prioritaires dans l'audience du mois.
        [$from, $to] = $this->monthRange($month);
        $total = PageView::whereBetween('created_at', [$from, $to])->distinct()->count('visitor_hash');
        if ($total >= 50) {
            $priority = PageView::whereBetween('created_at', [$from, $to])->whereIn('country', array_keys(Markets::all()))
                ->distinct()->count('visitor_hash');
            $share = (int) round($priority / $total * 100);
            if ($share < 70) {
                $tips[] = ['level' => 'info', 'text' => "Seulement {$share} % des visiteurs viennent de nos 5 marchés prioritaires. Ciblez davantage ces pays dans les articles et les publicités (Facebook permet de cibler par pays)."];
            }
        }

        if ($rows->isNotEmpty() && $rows->every(fn ($r) => $r->ratio >= 1)) {
            $tips[] = ['level' => 'success', 'text' => 'Tous les objectifs sont atteints : augmentez-les de 20 à 30 % le mois prochain (bouton « Préparer le mois suivant »).'];
        }

        if ($tips === []) {
            $tips[] = ['level' => 'success', 'text' => 'Rien d’alarmant : gardez le rythme de publication et comparez la semaine prochaine.'];
        }

        return $tips;
    }
}
