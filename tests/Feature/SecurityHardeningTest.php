<?php

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('private');
    Storage::fake('public');
});

function paidPdfExercise(): Exercise
{
    Storage::disk('private')->put('exercises/pdf/sujet.pdf', '%PDF-1.4 sujet payant');

    return Exercise::create([
        'title' => 'Sujet payant', 'exercise_type' => 'pdf', 'pdf_path' => 'exercises/pdf/sujet.pdf',
        'is_paid' => true, 'price_cents' => 1000, 'currency' => 'TND', 'is_published' => true,
    ]);
}

it('never exposes a paid exercise PDF through a public link', function () {
    $exercise = paidPdfExercise();
    $url = route('exercise.media', ['exercise' => $exercise, 'kind' => 'pdf']);

    // Invité → connexion ; élève qui n'a pas acheté → refusé.
    $this->get($url)->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->get($url)->assertForbidden();

    expect(Storage::disk('public')->exists('exercises/pdf/sujet.pdf'))->toBeFalse();
});

it('serves the exercise PDF to admins for preview', function () {
    $exercise = paidPdfExercise();
    Role::findOrCreate('admin');
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get(route('exercise.media', ['exercise' => $exercise, 'kind' => 'pdf']));

    $response->assertOk();
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
});

it('moves legacy exercise files from the public disk to the private disk', function () {
    Storage::disk('public')->put('exercises/videos/cours.mp4', 'video');
    Exercise::create(['title' => 'Vidéo', 'exercise_type' => 'video_interactive', 'video_path' => 'exercises/videos/cours.mp4',
        'is_paid' => false, 'is_published' => true]);

    $migration = require database_path('migrations/2026_11_02_120100_move_exercise_media_to_private_disk.php');
    $migration->up();

    expect(Storage::disk('private')->exists('exercises/videos/cours.mp4'))->toBeTrue()
        ->and(Storage::disk('public')->exists('exercises/videos/cours.mp4'))->toBeFalse();
});

it('no longer exposes the geolocation diagnostic route nor the PHP version', function () {
    $this->get('/_health/geo')->assertNotFound();

    $response = $this->get(route('home'));
    expect($response->headers->get('Content-Security-Policy'))->toContain("frame-ancestors 'self'")
        ->toContain("object-src 'none'");
});

it('creates the real admin account from the console, replacing the default one', function () {
    Role::findOrCreate('admin');
    $old = User::factory()->create(['email' => 'admin@zaytouna.local']);
    $old->assignRole('admin');
    $other = User::factory()->create(['email' => 'ancien@exemple.com']);
    $other->assignRole('admin');

    $this->artisan('zaytouna:admin', ['email' => 'direction@exemple.com', '--replace' => 'admin@zaytouna.local', '--demote-others' => true])
        ->expectsQuestion('Mot de passe (12 caractères min., majuscules, minuscules, chiffres et symboles)', 'Tr3s-Solide!Mdp')
        ->expectsQuestion('Confirmez le mot de passe', 'Tr3s-Solide!Mdp')
        ->assertSuccessful();

    $admin = User::where('email', 'direction@exemple.com')->sole();
    expect($admin->id)->toBe($old->id)
        ->and($admin->hasRole('admin'))->toBeTrue()
        ->and(\Illuminate\Support\Facades\Hash::check('Tr3s-Solide!Mdp', $admin->password))->toBeTrue()
        ->and(User::where('email', 'admin@zaytouna.local')->exists())->toBeFalse()
        ->and($other->fresh()->hasRole('admin'))->toBeFalse();
});

it('refuses a weak admin password', function () {
    $this->artisan('zaytouna:admin', ['email' => 'direction@exemple.com'])
        ->expectsQuestion('Mot de passe (12 caractères min., majuscules, minuscules, chiffres et symboles)', 'Admin@12345')
        ->expectsQuestion('Confirmez le mot de passe', 'Admin@12345')
        ->assertFailed();

    expect(User::where('email', 'direction@exemple.com')->exists())->toBeFalse();
});
