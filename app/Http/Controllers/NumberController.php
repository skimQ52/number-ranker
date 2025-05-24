<?php

namespace App\Http\Controllers;

use App\Models\Number;
use App\Services\EloService;
use App\Services\NumberService;
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
            'winner' => 'required|integer|between:1,99',
            'loser' => 'required|integer|between:1,99'
        ]);

        if (env('VOTE_RATE_LIMIT_ENABLED', false)) {
            $ip = $request->ip();
            $cacheKey = "votes_by_ip_{$ip}";

            $votes = Cache::get($cacheKey, 0);
            if ($votes >= 100) {
                return response()->json(['message' => 'Vote limit reached'], 429);
            }

            if (!Cache::has($cacheKey)) {
                Cache::put($cacheKey, 1, Carbon::now('America/New_York')->endOfDay());
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
            Cache::put($cacheKey, 1, Carbon::now('America/New_York')->endOfDay());
        } else {
            Cache::increment($cacheKey);
        }

        return $this->returnForNextVote();
    }

    public function duo(Request $request): JsonResponse
    {
        if (env('VOTE_RATE_LIMIT_ENABLED', false)) {
            $ip = $request->ip();
            $cacheKey = "votes_by_ip_{$ip}";

            $votes = Cache::get($cacheKey, 0);
            if ($votes >= 100) {
                return response()->json(['message' => 'Vote limit reached'], 429);
            }
        }

        return $this->returnForNextVote();
    }

    public function index(): JsonResponse
    {
        $numbers = DB::table('numbers')->orderBy('elo', 'desc')->get();

        return response()->json($numbers);
    }

    /**
     * @return JsonResponse
     */
    public function returnForNextVote(): JsonResponse
    {
        [$leftNumber, $rightNumber] = NumberService::duo();
        $votesToday = Cache::get('votes_' . Carbon::today()->toDateString(), 0);
        $totalVotes = Number::query()->sum('wins');

        return response()->json([
            'left' => $leftNumber->number,
            'right' => $rightNumber->number,
            'votes' => $votesToday,
            'total' => $totalVotes,
        ]);
    }
}
