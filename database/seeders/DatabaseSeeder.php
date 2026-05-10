<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
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
            
        ]);

        $admin = User::updateOrCreate(
            ['email' => 'admin@zaytouna.local'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin@12345'),
            ]
        );

        $admin->assignRole('admin');
    }           
}