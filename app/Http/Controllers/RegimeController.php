<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Level;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Subject;
use App\Models\Track;
use App\Models\User;
use App\Notifications\RegimeOrderPending;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegimeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Public pages by country
    |--------------------------------------------------------------------------
    */

    public function showTunisia(Request $request)
    {
        return $this->showBySlug($request, 'tunisia');
    }

    public function showQatar(Request $request)
    {
        return $this->showBySlug($request, 'qatar');
    }

    public function showSaudi(Request $request)
    {
        return $this->showBySlug($request, 'saudi');
    }

    public function showQuran(Request $request)
    {
        return $this->showBySlug($request, 'quran');
    }

    public function startTunisia(Request $request)
    {
        return $this->startBySlug($request, 'tunisia');
    }

    public function startQatar(Request $request)
    {
        return $this->startBySlug($request, 'qatar');
    }

    public function startSaudi(Request $request)
    {
        return $this->startBySlug($request, 'saudi');
    }

    public function checkoutTunisia(Request $request)
    {
        return $this->checkoutBySlug($request, 'tunisia');
    }

    public function checkoutQatar(Request $request)
    {
        return $this->checkoutBySlug($request, 'qatar');
    }

    public function checkoutSaudi(Request $request)
    {
        return $this->checkoutBySlug($request, 'saudi');
    }

    public function startQuran(Request $request)
    {
        return $this->startBySlug($request, 'quran');
    }

    public function checkoutQuran(Request $request)
    {
        return $this->checkoutBySlug($request, 'quran');
    }

    public function showFrance(Request $request)
    {
        return $this->showBySlug($request, 'france');
    }

    public function startFrance(Request $request)
    {
        return $this->startBySlug($request, 'france');
    }

    public function checkoutFrance(Request $request)
    {
        return $this->checkoutBySlug($request, 'france');
    }

    public function submitTunisia(Request $request) { return $this->submitBySlug($request, 'tunisia'); }
    public function submitQatar(Request $request)   { return $this->submitBySlug($request, 'qatar'); }
    public function submitSaudi(Request $request)   { return $this->submitBySlug($request, 'saudi'); }
    public function submitQuran(Request $request)   { return $this->submitBySlug($request, 'quran'); }
    public function submitFrance(Request $request)  { return $this->submitBySlug($request, 'france'); }

    /*
    |--------------------------------------------------------------------------
    | Core logic
    |--------------------------------------------------------------------------
    */

    private function showBySlug(Request $request, string $slug)
    {
        $track = $this->getActiveTrackOrFail($slug);

        $track->load([
            'levels' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'levels.branches' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
        ]);

        $levels = $track->levels;

        $branches = Branch::query()
            ->where('track_id', $track->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $subjects = Subject::query()
            ->where('is_active', true)
            ->whereHas('level', fn ($q) => $q->where('track_id', $track->id))
            ->orderBy('sort_order')
            ->get();

        // Devise du visiteur (géo-détection ou choix manuel), repli sur la devise du régime
        $regimeCurrency  = CurrencyService::REGIME_CURRENCY[$slug] ?? 'TND';
        $visitorCurrency = $this->visitorCurrencyFor($slug);
        $packs = $this->getPacksForTrack($slug, $visitorCurrency);

        $groupLabels = match ($slug) {
            'saudi' => [
                'primary' => 'Ecole primaire',
                'college' => 'College',
                'lycee'   => 'Lycee',
            ],
            'quran' => [
                'quran' => 'Quran',
            ],
            default => [
                'primary' => 'Primaire',
                'middle'  => 'College',
                'lycee'   => 'Lycee',
            ],
        };

        $currencySymbol = CurrencyService::SYMBOLS[$visitorCurrency] ?? $visitorCurrency;
        $modePrices     = CurrencyService::modeHourPrices($visitorCurrency);

        return view("regimes.{$slug}.show", compact(
            'track',
            'levels',
            'branches',
            'subjects',
            'packs',
            'groupLabels',
            'visitorCurrency',
            'regimeCurrency',
            'currencySymbol',
            'modePrices'
        ));
    }

    private function startBySlug(Request $request, string $slug)
    {
        $track = $this->getActiveTrackOrFail($slug);

        $data = $request->validate([
            'pack'       => ['required', 'string', 'in:individual,duo,group,recorded,custom'],
            'level_id'   => ['nullable', 'integer'],
            'branch_id'  => ['nullable', 'integer'],
            'grade'      => ['nullable', 'string', 'max:60'],
            'subject_id' => ['nullable', 'integer'],
            'plan'       => ['nullable', 'string', 'in:monthly,quarterly,yearly'],
            'items'      => ['nullable', 'string'],
        ]);

        if ($data['pack'] === 'custom') {
            $plan = $data['plan'] ?? 'yearly';
            $items = json_decode($data['items'] ?? '[]', true);

            if (!is_array($items) || empty($items)) {
                return redirect()->route("regimes.{$slug}.show")
                    ->with('error', 'Ajoutez au moins une matière dans le pack personnalisé.');
            }

            // Devise du visiteur (verrouillée dans la sélection ci-dessous)
            $visitorCurrency = $this->visitorCurrencyFor($slug);

            $hourPrice = CurrencyService::packPrice('hour', $visitorCurrency);
            $discounts = ['monthly' => 0.00, 'quarterly' => 0.10, 'yearly' => 0.20];
            $monthsByPlan = ['monthly' => 1, 'quarterly' => 3, 'yearly' => 12];

            $months = $monthsByPlan[$plan] ?? 12;
            $discountRate = $discounts[$plan] ?? 0.20;

            $computedItems = [];
            $subtotalMonthly = 0;

            foreach ($items as $idx => $it) {
                $levelId = (int) ($it['level_id'] ?? 0);
                $branchId = !empty($it['branch_id']) ? (int) $it['branch_id'] : null;
                $subjectId = (int) ($it['subject_id'] ?? 0);
                $duration = (int) ($it['duration_h'] ?? 0);
                $spm = (int) ($it['sessions_per_month'] ?? 0);
                $child = trim((string) ($it['child'] ?? $it['student_name'] ?? ''));

                if ($levelId <= 0 || $subjectId <= 0 || !in_array($duration, [1, 2], true) || $spm < 1) {
                    return redirect()->route("regimes.{$slug}.show")
                        ->with('error', 'Ligne ' . ($idx + 1) . ' invalide.');
                }

                $level = $track->levels()
                    ->where('id', $levelId)
                    ->where('is_active', true)
                    ->first();

                if (!$level) {
                    return redirect()->route("regimes.{$slug}.show")
                        ->with('error', 'Niveau invalide (ligne ' . ($idx + 1) . ').');
                }

                $levelHasBranches = Branch::query()
                    ->where('level_id', $level->id)
                    ->where('is_active', true)
                    ->exists();

                if ($levelHasBranches && !$branchId) {
                    return redirect()->route("regimes.{$slug}.show")
                        ->with('error', 'Branche requise (ligne ' . ($idx + 1) . ').');
                }

                if ($branchId) {
                    $branchOk = Branch::query()
                        ->where('id', $branchId)
                        ->where('level_id', $level->id)
                        ->where('track_id', $track->id)
                        ->where('is_active', true)
                        ->exists();

                    if (!$branchOk) {
                        return redirect()->route("regimes.{$slug}.show")
                            ->with('error', 'Branche invalide (ligne ' . ($idx + 1) . ').');
                    }
                }

                $subjectOk = Subject::query()
                    ->where('id', $subjectId)
                    ->where('level_id', $level->id)
                    ->where('is_active', true)
                    ->when($levelHasBranches, fn ($q) => $q->where('branch_id', $branchId))
                    ->when(!$levelHasBranches, fn ($q) => $q->whereNull('branch_id'))
                    ->exists();

                if (!$subjectOk) {
                    return redirect()->route("regimes.{$slug}.show")
                        ->with('error', 'Matière invalide (ligne ' . ($idx + 1) . ').');
                }

                $lineMonthly = $hourPrice * $duration * $spm;
                $subtotalMonthly += $lineMonthly;

                $computedItems[] = [
                    'child' => $child,
                    'level_id' => $levelId,
                    'branch_id' => $branchId,
                    'subject_id' => $subjectId,
                    'duration_h' => $duration,
                    'sessions_per_month' => $spm,
                    'line_monthly' => $lineMonthly,
                ];
            }

            $sub = $subtotalMonthly * $months;
            $disc = round($sub * $discountRate);
            $total = round($sub - $disc);

            session([
                'regime.selection' => [
                    'track_id' => $track->id,
                    'pack' => 'custom',
                    'plan' => $plan,
                    'pricing' => [
                        'currency' => $visitorCurrency,
                        'hour_price' => $hourPrice,
                        'months' => $months,
                        'discount_rate' => $discountRate,
                        'subtotal_cents' => (int) round($sub * 100),
                        'discount_cents' => (int) round($disc * 100),
                        'total_cents' => (int) round($total * 100),
                        'subtotal_monthly_cents' => (int) round($subtotalMonthly * 100),
                    ],
                    'items' => $computedItems,
                ],
            ]);

            if (!auth()->check()) {
                session(['url.intended' => route("regimes.{$slug}.checkout")]);
                return redirect()->route('login');
            }

            return redirect()->route("regimes.{$slug}.checkout");
        }

        if (empty($data['level_id']) || empty($data['subject_id'])) {
            return redirect()->route("regimes.{$slug}.show")
                ->with('error', 'Veuillez choisir le niveau et la matière.');
        }

        $level = $track->levels()
            ->where('id', $data['level_id'])
            ->where('is_active', true)
            ->first();

        if (!$level) {
            return redirect()->route("regimes.{$slug}.show")
                ->with('error', 'Niveau invalide.');
        }

        $levelHasBranches = Branch::query()
            ->where('level_id', $level->id)
            ->where('is_active', true)
            ->exists();

        $branchId = !empty($data['branch_id']) ? (int) $data['branch_id'] : null;

        if ($levelHasBranches && !$branchId) {
            return redirect()->route("regimes.{$slug}.show")
                ->with('error', 'Veuillez choisir une branche.');
        }

        if ($branchId) {
            $branchOk = Branch::query()
                ->where('id', $branchId)
                ->where('level_id', $level->id)
                ->where('track_id', $track->id)
                ->where('is_active', true)
                ->exists();

            if (!$branchOk) {
                return redirect()->route("regimes.{$slug}.show")
                    ->with('error', 'Branche invalide.');
            }
        }

        $subjectOk = Subject::query()
            ->where('id', $data['subject_id'])
            ->where('level_id', $level->id)
            ->where('is_active', true)
            ->when($levelHasBranches, fn ($q) => $q->where('branch_id', $branchId))
            ->when(!$levelHasBranches, fn ($q) => $q->whereNull('branch_id'))
            ->exists();

        if (!$subjectOk) {
            return redirect()->route("regimes.{$slug}.show")
                ->with('error', 'Matière invalide.');
        }

        session([
            'regime.selection' => [
                'track_id'   => $track->id,
                'level_id'   => (int) $data['level_id'],
                'branch_id'  => $branchId,
                'grade'      => $data['grade'] ?? null,
                'subject_id' => (int) $data['subject_id'],
                'pack'       => $data['pack'],
            ],
        ]);

        if (!auth()->check()) {
            session(['url.intended' => route("regimes.{$slug}.checkout")]);
            return redirect()->route('login');
        }

        return redirect()->route("regimes.{$slug}.checkout");
    }

    private function submitBySlug(Request $request, string $slug)
    {
        $track = $this->getActiveTrackOrFail($slug);

        $sel = session('regime.selection');

        if (! $sel || (int) ($sel['track_id'] ?? 0) !== (int) $track->id) {
            return redirect()->route("regimes.{$slug}.show")
                ->with('error', 'Votre sélection a expiré. Veuillez recommencer.');
        }

        $user     = $request->user();
        $isCustom = ($sel['pack'] ?? '') === 'custom';
        $currency = $sel['pricing']['currency'] ?? $this->visitorCurrencyFor($slug);

        // For custom packs the total is computed; for classic packs it's TBD by admin
        $totalCents = $isCustom
            ? (int) ($sel['pricing']['total_cents'] ?? 0)
            : 0;

        $order = DB::transaction(function () use ($user, $sel, $slug, $currency, $totalCents) {
            $order = Order::create([
                'user_id'        => $user->id,
                'status'         => 'pending_manual',
                'subtotal_cents' => $totalCents,
                'discount_cents' => (int) ($sel['pricing']['discount_cents'] ?? 0),
                'total_cents'    => $totalCents,
                'currency'       => $currency,
            ]);

            // No order_items (regime = live service, no course_id)
            // All details stored in payment payload

            Payment::create([
                'order_id'         => $order->id,
                'provider'         => 'regime',
                'status'           => 'pending',
                'transaction_id'   => null,
                'payment_url'      => null,
                'provider_payload' => [
                    'regime'    => $slug,
                    'selection' => $sel,
                    'currency'  => $currency,
                ],
            ]);

            return $order;
        });

        session()->forget('regime.selection');

        // Notify admins
        $order->load('user');
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new RegimeOrderPending($order, $slug, $sel));
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Votre demande a bien été envoyée. L\'équipe Zaytouna vous contactera sous 24h pour confirmer votre planning.');
    }

    private function checkoutBySlug(Request $request, string $slug)
    {
        $track = $this->getActiveTrackOrFail($slug);

        $sel = session('regime.selection');

        if (!$sel || (int) ($sel['track_id'] ?? 0) !== (int) $track->id) {
            return redirect()->route("regimes.{$slug}.show")
                ->with('error', 'Veuillez choisir un pack avant de passer au paiement.');
        }

        $maps = ['levels' => [], 'branches' => [], 'subjects' => []];

        if (($sel['pack'] ?? '') === 'custom') {
            $levelIds = collect($sel['items'] ?? [])->pluck('level_id')->unique()->filter()->values()->all();
            $branchIds = collect($sel['items'] ?? [])->pluck('branch_id')->unique()->filter()->values()->all();
            $subjectIds = collect($sel['items'] ?? [])->pluck('subject_id')->unique()->filter()->values()->all();

            $maps['levels'] = Level::query()->whereIn('id', $levelIds)->pluck('name', 'id')->all();
            $maps['branches'] = Branch::query()->whereIn('id', $branchIds)->pluck('name', 'id')->all();
            $maps['subjects'] = Subject::query()->whereIn('id', $subjectIds)->pluck('name', 'id')->all();
        }

        $regimeCurrency  = CurrencyService::REGIME_CURRENCY[$slug] ?? 'TND';
        $visitorCurrency = $sel['pricing']['currency'] ?? $this->visitorCurrencyFor($slug);
        $backRoute       = route("regimes.{$slug}.show");

        return view("regimes.{$slug}.checkout", [
            'track'           => $track,
            'selection'       => $sel,
            'maps'            => $maps,
            'visitorCurrency' => $visitorCurrency,
            'regimeCurrency'  => $regimeCurrency,
            'backRoute'       => $backRoute,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function getActiveTrackOrFail(string $slug): Track
    {
        return Track::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    /**
     * Currency to display regime prices in: the visitor's currency when it
     * comes from a real signal (manual pick or geolocation), otherwise the
     * regime's own billing currency.
     */
    private function visitorCurrencyFor(string $slug): string
    {
        return CurrencyService::hasExplicitChoice()
            ? CurrencyService::current()
            : (CurrencyService::REGIME_CURRENCY[$slug] ?? 'TND');
    }

    private function getPacksForTrack(string $slug, string $currency = 'TND'): array
    {
        $sym     = CurrencyService::SYMBOLS[$currency] ?? $currency;
        $indiv   = CurrencyService::formatPack('individual', $currency);
        $duo     = CurrencyService::formatPack('duo', $currency);
        $group   = CurrencyService::formatPack('group', $currency);
        $rec     = CurrencyService::formatPack('recorded', $currency, 'quarterly');
        $hourAmt = CurrencyService::packPrice('hour', $currency);
        $custom  = "À la séance • {$hourAmt} {$sym} / heure";

        $sharedPacks = [
            [
                'key' => 'individual',
                'title' => 'Pack Individuel',
                'price' => $indiv,
                'price_amount' => CurrencyService::packPrice('individual', $currency),
                'currency' => $currency,
                'features' => ['Cours live 1 à 1', 'Planning flexible', 'Professeur dédié'],
                'highlight' => true,
            ],
            [
                'key' => 'duo',
                'title' => 'Pack Duo',
                'price' => $duo,
                'price_amount' => CurrencyService::packPrice('duo', $currency),
                'currency' => $currency,
                'features' => ['2 étudiants par professeur', 'Sessions live partagées', 'Suivi de progression'],
                'highlight' => false,
            ],
            [
                'key' => 'group',
                'title' => 'Pack Groupe',
                'price' => $group,
                'price_amount' => CurrencyService::packPrice('group', $currency),
                'currency' => $currency,
                'features' => ['Petit groupe (max 6)', 'Sessions planifiées', 'Support communauté'],
                'highlight' => false,
            ],
            [
                'key' => 'recorded',
                'title' => 'Contenu enregistré',
                'price' => $rec,
                'price_amount' => CurrencyService::packPrice('recorded', $currency),
                'currency' => $currency,
                'features' => ['Résumés de cours', 'Exercices corrigés', 'Sessions enregistrées', 'Corrections d\'examens'],
                'highlight' => false,
            ],
            [
                'key' => 'custom',
                'title' => 'Pack Personnalisé',
                'price' => $custom,
                'price_amount' => $hourAmt,
                'currency' => $currency,
                'features' => ['Plusieurs matières & classes', 'Séances 1h ou 2h', 'Mensuel / Trimestriel / Annuel (recommandé)'],
                'highlight' => true,
            ],
        ];

        if ($slug === 'quran') {
            $sharedPacks[3]['title']    = 'Récitations enregistrées';
            $sharedPacks[3]['features'] = ['Récitations par sourate', 'Règles de tajweed illustrées', 'Accès illimité'];
            $sharedPacks[4]['features'] = ['Plusieurs niveaux', 'Séances 1h ou 2h', 'Mensuel / Trimestriel / Annuel'];
        }

        return in_array($slug, ['tunisia', 'qatar', 'saudi', 'quran', 'france'], true) ? $sharedPacks : [];
    }

    public function index()
    {
        $systems = [
            [
                'key' => 'tunisia',
                'title' => __('ui.regimes_index.filters.tunisia'),
                'text' => __('ui.regimes_systems.tunisia_text'),
                'image' => asset('images/tunisie.png'),
                'route' => route('regimes.tunisia.show'),
            ],
            [
                'key' => 'qatar',
                'title' => 'Qatar',
                'text' => 'Explorez le système éducatif qatari.',
                'image' => asset('images/qatar.png'),
                'route' => route('regimes.qatar.show'),
            ],
            [
                'key' => 'saudi',
                'title' => 'Arabie saoudite',
                'text' => 'Découvrez le parcours saoudien.',
                'image' => asset('images/saudite.png'),
                'route' => route('regimes.saudi.show'),
            ],
            [
                'key' => 'quran',
                'title' => 'Quran',
                'text' => 'Apprentissage du Quran : lecture, tajweed, memorisation et tafsir.',
                'image' => asset('images/Quran.png'),
                'route' => route('regimes.quran.show'),
            ],
            [
                'key' => 'france',
                'title' => __('ui.regimes_index.filters.france'),
                'text' => __('ui.regimes_systems.france_text'),
                'image' => asset('images/france.jpg'),
                'route' => route('regimes.france.show'),
            ],
        ];

        return view('regimes.index', compact('systems'));
    }
}