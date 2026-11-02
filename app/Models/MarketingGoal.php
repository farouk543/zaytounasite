<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * Objectif mensuel (ex. « 300 visiteurs depuis la Tunisie en novembre 2026 »).
 * Le « réalisé » est calculé à la volée par GoalTracker à partir des statistiques.
 */
class MarketingGoal extends Model
{
    /** Indicateurs disponibles : libellé, unité, aide, et si l'objectif peut viser un pays. */
    public const METRICS = [
        'visitors' => ['label' => 'Visiteurs uniques', 'unit' => '', 'country' => true,
            'help' => 'Personnes différentes venues sur le site.'],
        'google_visitors' => ['label' => 'Visiteurs venus de Google', 'unit' => '', 'country' => true,
            'help' => 'Le vrai thermomètre du SEO : il doit monter chaque mois.'],
        'blog_readers' => ['label' => 'Lecteurs du blog', 'unit' => '', 'country' => true,
            'help' => 'Visiteurs ayant lu au moins un article.'],
        'signups' => ['label' => 'Inscriptions', 'unit' => '', 'country' => true,
            'help' => 'Nouveaux comptes (pays = pays de navigation de l’inscrit).'],
        'conversion' => ['label' => 'Taux de conversion', 'unit' => '%', 'country' => true,
            'help' => 'Inscriptions ÷ visiteurs uniques.'],
        'orders' => ['label' => 'Demandes de commande', 'unit' => '', 'country' => true,
            'help' => 'Commandes créées (cours, packs, régimes).'],
        'whatsapp' => ['label' => 'Contacts WhatsApp', 'unit' => '', 'country' => false,
            'help' => 'Visiteurs ayant cliqué sur un bouton WhatsApp du site (bouton flottant, Club…).'],
        'paid' => ['label' => 'Commandes payées', 'unit' => '', 'country' => true,
            'help' => 'Commandes validées / payées.'],
        // Plateforme d'enseignement (API de statistiques) : ce qui compte vraiment, des élèves qui suivent les cours.
        'active_students' => ['label' => 'Élèves actifs (plateforme)', 'unit' => '', 'country' => true, 'platform' => true, 'project' => false,
            'help' => 'Élèves présents à au moins une séance dans le mois (pays déduit du fuseau horaire).'],
        'retention' => ['label' => 'Fidélisation des élèves', 'unit' => '%', 'country' => false, 'platform' => true,
            'help' => 'Part des élèves actifs le mois précédent qui sont encore actifs ce mois-ci.'],
        'attendance' => ['label' => 'Assiduité aux séances', 'unit' => '%', 'country' => false, 'platform' => true,
            'help' => 'Présences ÷ (présences + absences) sur les séances du mois.'],
        'articles' => ['label' => 'Articles publiés', 'unit' => '', 'country' => false,
            'help' => 'Rythme éditorial du blog.'],
    ];

    protected $fillable = ['month', 'metric', 'country', 'target', 'notes'];

    protected $casts = ['target' => 'float'];

    public function metricLabel(): string
    {
        return self::METRICS[$this->metric]['label'] ?? $this->metric;
    }

    public function unit(): string
    {
        return self::METRICS[$this->metric]['unit'] ?? '';
    }

    /** Indicateur fourni par la plateforme d'enseignement (et non par les statistiques du site). */
    public function isPlatform(): bool
    {
        return (bool) (self::METRICS[$this->metric]['platform'] ?? false);
    }

    public function isRate(): bool
    {
        return $this->unit() === '%';
    }

    public function monthStart(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d', $this->month.'-01', config('marketing.timezone'))->startOfDay();
    }

    public function monthLabel(): string
    {
        return ucfirst($this->monthStart()->locale('fr')->isoFormat('MMMM YYYY'));
    }

    public static function monthOptions(int $back = 3, int $ahead = 6): array
    {
        $options = [];
        $start = CarbonImmutable::now(config('marketing.timezone'))->startOfMonth();
        for ($i = -$back; $i <= $ahead; $i++) {
            $m = $start->addMonthsNoOverflow($i);
            $options[$m->format('Y-m')] = ucfirst($m->locale('fr')->isoFormat('MMMM YYYY'));
        }

        return $options;
    }
}
