<?php

namespace App\Http\Controllers;

use App\Models\Number;
use App\Services\EloService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NumberController extends Controller
{
    public function vote(EloService $eloService, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'winner' => 'required|integer',
            'loser' => 'required|integer'
        ]);

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
