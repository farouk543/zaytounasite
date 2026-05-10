<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Track;

class TrackSeeder extends Seeder
{
    public function run(): void
    {
        $tracks = [
            ['slug' => 'tunisia', 'name' => 'Tunisia', 'name_ar' => 'تونس', 'sort_order' => 1],
            ['slug' => 'qatar', 'name' => 'Qatar', 'name_ar' => 'قطر', 'sort_order' => 2],
            ['slug' => 'saudi', 'name' => 'Saudi Arabia', 'name_ar' => 'السعودية', 'sort_order' => 3],
            ['slug' => 'quran', 'name' => 'Quran', 'name_ar' => 'القرآن الكريم', 'sort_order' => 4],
        ];

        foreach ($tracks as $t) {
            Track::updateOrCreate(['slug' => $t['slug']], $t);
        }
    }
}