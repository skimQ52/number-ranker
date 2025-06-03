<?php

// app/Console/Commands/SyncVoteCounts.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Number;

class SyncVoteCounts extends Command
{
    protected $signature = 'votes:sync-counts';
    protected $description = 'Sync wins and losses columns with actual vote counts';

    public function handle()
    {
        $this->info('Syncing wins and losses...');

        Number::query()->chunk(100, function ($numbers) {
            foreach ($numbers as $number) {
                $wins = $number->wins()->count();
                $losses = $number->losses()->count();

                $number->update([
                    'wins' => $wins,
                    'losses' => $losses
                ]);
            }
        });

        $this->info('Sync complete.');
    }
}
