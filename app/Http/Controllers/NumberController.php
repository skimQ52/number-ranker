<?php

namespace App\Http\Controllers;

use App\Models\Number;
use App\Services\EloService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NumberController extends Controller
{
    public function vote(EloService $eloService, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'winner' => 'required|integer',
            'loser' => 'required|integer'
        ]);

        if (env('VOTE_RATE_LIMIT_ENABLED', false)) {
            $ip = $request->ip();
            $cacheKey = "votes_by_ip_{$ip}";

            $votes = Cache::get($cacheKey, 0);
            if ($votes >= 15) {
                return response()->json(['message' => 'Vote limit reached'], 429);
            }

            if (!Cache::has($cacheKey)) {
                Cache::put($cacheKey, 1, now()->endOfDay());
            } else {
                Cache::increment($cacheKey);
            }
        }


        $winner = Number::query()->find($validated['winner']);
        $loser = Number::query()->find($validated['loser']);

        $newWinnerElo = $eloService->win($winner->elo, $loser->elo);
        $newLoserElo = $eloService->loss($loser->elo, $winner->elo);

        $winner->update([
            'elo' => $newWinnerElo,
            'wins' => $winner->wins + 1,
        ]);

        $loser->update([
            'elo' => $newLoserElo,
            'losses' => $winner->losses + 1,
        ]);

        $cacheKey = 'votes_' . Carbon::today()->toDateString();

        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, 1, now()->endOfDay());
        } else {
            Cache::increment($cacheKey);
        }

        $votesToday = Cache::get('votes_' . Carbon::today()->toDateString(), 0);
        logger()->debug('votes today after voting'.$votesToday);

        return response()->json(['Winner' => $winner, 'Loser' => $loser], 200);
    }

    public function duo(Request $request): JsonResponse
    {
        if (env('VOTE_RATE_LIMIT_ENABLED', false)) {
            $ip = $request->ip();
            $cacheKey = "votes_by_ip_{$ip}";

            $votes = Cache::get($cacheKey, 0);
            if ($votes >= 15) {
                return response()->json(['message' => 'Vote limit reached'], 429);
            }
        }

        $randomLeft = rand(1,100);
        $randomRight = rand(1,100);

        while($randomLeft === $randomRight) {
            $randomRight = rand(1,100);
        }

        $leftNumber = Number::query()->find($randomLeft);
        $rightNumber = Number::query()->find($randomRight);

        $votesToday = Cache::get('votes_' . Carbon::today()->toDateString(), 0);
        logger()->debug($votesToday);

        return response()->json([
            'left' => $leftNumber->number,
            'right' => $rightNumber->number,
            'votes' => $votesToday,
        ]);
    }

    public function index(): JsonResponse
    {
        $numbers = DB::table('numbers')->orderBy('elo', 'desc')->get();

        return response()->json($numbers);
    }
}
