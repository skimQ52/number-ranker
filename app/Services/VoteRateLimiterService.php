<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class VoteRateLimiterService
{
    public function checkVoteLimits(string $ip, int $winner, int $loser): ?JsonResponse
    {
        // Daily limit per IP
        $ipKey = "votes_by_ip_{$ip}";
        $votes = Cache::get($ipKey, 0);
        if ($votes >= 120) {
             return response()->json(['message' => 'Vote limit reached'], 429);
        }

        if (!Cache::has($ipKey)) {
            Cache::put($ipKey, 1, now()->addHours(8));
        } else {
            Cache::increment($ipKey);
        }

        // Match-specific limit
        $matchKey = "vote:" . md5($ip . $winner . $loser);
        $matchVotes = Cache::get($matchKey, 0);
        if ($matchVotes >= 3) {
            return response()->json(['message' => 'Armand-ing Detected'], 429);
        }
        Cache::put($matchKey, $matchVotes + 1, now()->addHours(8));

        // Repeated winner streak
        $streakKey = "vote_streak:$ip";
        $streak = Cache::get($streakKey, ['winner' => null, 'count' => 0]);

        if ($streak['winner'] == $winner) {
            $streak['count'] += 1;
        } else {
            $streak['winner'] = $winner;
            $streak['count'] = 1;
        }

        if ($streak['count'] > 3) {
            return response()->json(['message' => 'Armand-ing Detected'], 429);
        }

        Cache::put($streakKey, $streak, now()->addHours(8));

        return null;
    }
}
