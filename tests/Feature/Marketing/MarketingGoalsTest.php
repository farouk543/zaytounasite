<?php

use App\Models\MarketingGoal;
use App\Models\PageView;
use App\Models\User;
use App\Services\Analytics\GoalTracker;
use Database\Seeders\MarketingGoalSeeder;
use Spatie\Permission\Models\Role;

function viewFrom(string $country, string $hash, string $source = 'Direct', string $at = '2026-11-10 10:00:00'): void
{
    PageView::create(['visitor_hash' => str_pad($hash, 64, '0'), 'path' => '/', 'source' => $source,
        'country' => $country, 'created_at' => $at]);
}

it('seeds launch goals for every priority market', function () {
    $this->seed(MarketingGoalSeeder::class);
    $this->seed(MarketingGoalSeeder::class); // idempotent

    expect(MarketingGoal::where('month', '2026-11')->count())->toBe(21)
        ->and(MarketingGoal::where('month', '2026-12')->where('metric', 'visitors')->where('country', 'CA')->value('target'))->toEqual(52.0)
        ->and(MarketingGoal::where('month', '2027-01')->where('metric', 'conversion')->value('target'))->toEqual(4.0);
});

it('measures goals by country, projects the month and scores the strategy', function () {
    $this->travelTo('2026-11-16 00:00:00'); // ≈ la moitié du mois écoulée (heure de Tunis)

    MarketingGoal::create(['month' => '2026-11', 'metric' => 'visitors', 'country' => 'TN', 'target' => 3]);
    MarketingGoal::create(['month' => '2026-11', 'metric' => 'visitors', 'country' => 'CA', 'target' => 10]);
    MarketingGoal::create(['month' => '2026-11', 'metric' => 'google_visitors', 'country' => null, 'target' => 1]);

    viewFrom('TN', 'a', 'Google');
    viewFrom('TN', 'b');
    viewFrom('CA', 'c');
    viewFrom('DE', 'd');
    viewFrom('TN', 'e', 'Direct', '2026-10-20 10:00:00'); // hors période

    $tracker = app(GoalTracker::class);
    $rows = $tracker->progress('2026-11')->keyBy(fn ($r) => $r->goal->metric.'|'.$r->country);

    expect($tracker->actual('visitors', '2026-11', 'TN'))->toEqual(2.0)
        ->and($rows['visitors|TN']->status)->toBe('En avance')        // 2 en mi-mois → projection ≈ 4 / 3
        ->and($rows['visitors|CA']->status)->toBe('En retard')        // 1 → projection ≈ 2 / 10
        ->and($rows['google_visitors|']->status)->toBe('En avance');

    $score = $tracker->score('2026-11');
    expect($score['score'])->toBeGreaterThan(50)->toBeLessThan(80);

    $tips = collect($tracker->recommendations('2026-11'))->pluck('text')->implode(' ');
    expect($tips)->toContain('Canada');
});

it('renders the goals admin page and the strategy score', function () {
    Role::findOrCreate('admin');
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $admin->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP'); // double authentification configurée
    $this->travelTo('2026-11-16 00:00:00');
    $this->seed(MarketingGoalSeeder::class);
    viewFrom('FR', 'x', 'Google');

    $this->actingAs($admin)->get('/admin/objectifs')->assertOk()->assertSee('Visiteurs uniques')->assertSee('Canada');
    $this->get('/admin/statistiques')->assertOk()
        ->assertSee('Objectifs du mois')
        ->assertSee('Nos 5 marchés prioritaires')
        ->assertSee('Arabie saoudite')
        ->assertSee('/100');
});

it('prepares next month goals with growth, keeping rates and existing goals', function () {
    Role::findOrCreate('admin');
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $admin->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP'); // double authentification configurée
    MarketingGoal::create(['month' => '2026-11', 'metric' => 'visitors', 'country' => 'CA', 'target' => 40]);
    MarketingGoal::create(['month' => '2026-11', 'metric' => 'conversion', 'country' => null, 'target' => 4]);
    MarketingGoal::create(['month' => '2026-12', 'metric' => 'conversion', 'country' => null, 'target' => 5]);

    $this->actingAs($admin);
    \Livewire\Livewire::test(\App\Filament\Resources\MarketingGoals\Pages\ManageMarketingGoals::class)
        ->callAction('nextMonth', data: ['from' => '2026-11', 'growth' => 25])
        ->assertHasNoActionErrors();

    expect(MarketingGoal::where('month', '2026-12')->where('metric', 'visitors')->where('country', 'CA')->value('target'))->toEqual(50.0)
        ->and(MarketingGoal::where('month', '2026-12')->where('metric', 'conversion')->value('target'))->toEqual(5.0);
});

it('pulls students, retention and attendance from the platform stats API', function () {
    config(['zaytouna.platform_url' => 'https://plateforme.test', 'zaytouna.platform_stats_token' => 'secret-token']);
    \Illuminate\Support\Facades\Http::fake([
        'plateforme.test/api/marketing/stats*' => \Illuminate\Support\Facades\Http::response([
            'month' => '2026-11', 'students_total' => 40, 'new_students' => 6, 'active_students' => 31,
            'active_students_previous_month' => 30, 'retention_rate' => 60.0, 'attendance_rate' => 92.5,
            'seances_held' => 120, 'hours_taught' => 150.0, 'feedback_count' => 25, 'feedback_average' => 4.6,
            'by_country' => ['TN' => ['students_total' => 25, 'new_students' => 3, 'active_students' => 20],
                'CA' => ['students_total' => 3, 'new_students' => 2, 'active_students' => 2]],
        ]),
    ]);
    \Carbon\CarbonImmutable::setTestNow('2026-11-30 12:00:00');
    $this->travelTo(\Carbon\CarbonImmutable::now());

    MarketingGoal::create(['month' => '2026-11', 'metric' => 'retention', 'target' => 80]);
    MarketingGoal::create(['month' => '2026-11', 'metric' => 'attendance', 'target' => 90]);
    MarketingGoal::create(['month' => '2026-11', 'metric' => 'active_students', 'country' => 'CA', 'target' => 4]);

    $tracker = app(GoalTracker::class);
    $rows = $tracker->progress('2026-11')->keyBy(fn ($r) => $r->goal->metric);

    expect($rows['retention']->actual)->toBe(60.0)
        ->and($rows['attendance']->status)->toBeIn(['En avance', 'Atteint'])
        ->and($rows['active_students']->actual)->toBe(2.0)
        ->and($rows['active_students']->projected)->toEqual(2) // pas de projection pour des élèves distincts
        ->and(collect($tracker->recommendations('2026-11'))->pluck('text')->implode(' '))->toContain('Fidélisation à 60');

    \Illuminate\Support\Facades\Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer secret-token'));
});

it('marks platform goals as unavailable instead of failing when the API is down', function () {
    config(['zaytouna.platform_url' => 'https://plateforme.test', 'zaytouna.platform_stats_token' => 'secret-token']);
    \Illuminate\Support\Facades\Http::fake(['*' => \Illuminate\Support\Facades\Http::response('erreur', 500)]);
    MarketingGoal::create(['month' => now(config('marketing.timezone'))->format('Y-m'), 'metric' => 'retention', 'target' => 80]);

    $row = app(GoalTracker::class)->progress(now(config('marketing.timezone'))->format('Y-m'))->first();

    expect($row->status)->toBeIn(['Indisponible', 'Trop tôt']);
});
