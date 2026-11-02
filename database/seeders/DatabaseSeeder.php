<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            TrackSeeder::class,
            LevelSeeder::class,
            BranchSeeder::class,   // ← ajouté avant SubjectSeeder
            SubjectSeeder::class,
            MarketingSeeder::class, // blog (catégories + articles programmés) + objectifs
        ]);

        // Sécurité : aucun compte admin avec un mot de passe connu hors développement local.
        // En production, créer / modifier l'admin avec : php artisan zaytouna:admin <email>
        if (! app()->environment('local', 'testing')) {
            $this->command?->warn('Aucun compte admin créé : utilisez « php artisan zaytouna:admin <email> ».');

            return;
        }

        if (User::role('admin')->exists()) {
            return; // ne jamais écraser le mot de passe d'un admin existant
        }

        User::create([
            'name' => 'Admin (dev)',
            'email' => 'admin@zaytouna.local',
            'password' => Hash::make('Admin@12345'),
            'email_verified_at' => now(),
        ])->assignRole('admin');
    }
}
