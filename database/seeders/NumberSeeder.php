<?php

namespace Database\Seeders;

use App\Models\Number;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NumberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('numbers')->truncate(); // reset IDs

        for ($i = 1; $i < 100; $i++) {
            Number::factory()->create([
                'number' => $i,
                'wins' => 0,
                'losses' => 0,
                'elo' => 1500,
            ]);
        }

    }
}
