<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckElos extends Command
{
    protected $signature = 'app:check-elos';

    protected $description = 'Print all numbers with their Elo rating, sorted by Elo (highest first)';

    public function handle()
    {
        $numbers = DB::table('numbers')->orderBy('elo', 'desc')->get();

        $this->info("Number | Elo");
        $this->info(str_repeat('-', 20));

        foreach ($numbers as $number) {
            $this->line(sprintf("%6s | %4d", $number->number, $number->elo));
        }

    }
}
