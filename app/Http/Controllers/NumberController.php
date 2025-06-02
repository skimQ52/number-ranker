<?php

namespace App\Http\Controllers;

use App\Models\Number;
use App\Models\Vote;
use App\Services\EloService;
use App\Services\NumberService;
use App\Services\VoteRateLimiterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NumberController extends Controller
{
    public function vote(Request $request, EloService $eloService, VoteRateLimiterService $rateLimiter): JsonResponse
    {
        $validated = $request->validate([
            'winner' => 'required|integer|between:1,99',
            'loser' => 'required|integer|between:1,99'
        ]);

        $ip = $request->ip();

        $limitResponse = $rateLimiter->checkVoteLimits($ip, $validated['winner'], $validated['loser']);
        if ($limitResponse) {
            return $limitResponse;
        }

        $winner = Number::query()->find($validated['winner']);
        $loser = Number::query()->find($validated['loser']);

        Vote::query()->create([
            'winner_id' => $winner->id,
            'loser_id' => $loser->id,
        ]);

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

        $cacheKey = 'votes_' . Carbon::today('America/New_York')->toDateString();

        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, 1, Carbon::now('America/New_York')->endOfDay());
        } else {
            Cache::increment($cacheKey);
        }

        return $this->returnForNextVote();
    }

    public function duo(): JsonResponse
    {
        return $this->returnForNextVote();
    }

    public function index(): JsonResponse
    {
        $numbers = DB::table('numbers')->orderBy('elo', 'desc')->get();

        return response()->json($numbers);
    }

    public function returnForNextVote(): JsonResponse
    {
        [$leftNumber, $rightNumber] = NumberService::duo();
        $votesToday = Cache::get('votes_' . Carbon::today('America/New_York')->toDateString(), 0);
        $totalVotes = Number::query()->sum('wins');

        return response()->json([
            'left' => $leftNumber->number,
            'right' => $rightNumber->number,
            'votes' => $votesToday,
            'total' => $totalVotes,
        ]);
    }
}
