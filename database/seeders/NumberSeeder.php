<?php

namespace Database\Seeders;

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
        DB::table('users')->truncate(); // reset IDs

        for ($i = 1; $i < 100; $i++) {
            DB::table('numbers')->insert([
                'number' => $i,
                'elo' => 1500,
            ]);
        }

    }
}
