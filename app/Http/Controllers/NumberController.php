<?php

namespace App\Http\Controllers;

use App\Models\Number;
use App\Services\EloService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $ip = $request->ip();
        $cacheKey = "votes_by_ip_{$ip}";

        $votes = Cache::get($cacheKey, 0);
        if ($votes >= 15) {
            return response()->json(['message' => 'Vote limit reached'], 429);
        }

        Cache::put($cacheKey, $votes + 1, now()->addDay()); // Cache for a day

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

        return response()->json(['Winner' => $winner, 'Loser' => $loser], 200);
    }

    public function duo(Request $request): JsonResponse
    {
        $ip = $request->ip();
        $cacheKey = "votes_by_ip_{$ip}";

        $votes = Cache::get($cacheKey, 0);
        if ($votes >= 15) {
            return response()->json(['message' => 'Vote limit reached'], 429);
        }

        $randomLeft = rand(1,100);
        $randomRight = rand(1,100);

        while($randomLeft === $randomRight) {
            $randomRight = rand(1,100);
        }

        $leftNumber = Number::query()->find($randomLeft);
        $rightNumber = Number::query()->find($randomRight);

        return response()->json(['left' => $leftNumber->number, 'right' => $rightNumber->number]);
    }

    public function index(): JsonResponse
    {
        $numbers = DB::table('numbers')->orderBy('elo', 'desc')->get();

        return response()->json($numbers);
    }
}
