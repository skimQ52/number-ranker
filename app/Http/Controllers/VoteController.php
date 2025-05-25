<?php

namespace App\Http\Controllers;

use App\Models\Number;
use App\Models\Vote;
use Illuminate\Http\JsonResponse;

class VoteController
{
    public function votes(Number $number): JsonResponse
    {
        $allVotes = Vote::with(['winner', 'loser'])
            ->where('winner_id', $number->id)
            ->orWhere('loser_id', $number->id)
            ->orderByDesc('created_at')
            ->simplePaginate(10);

        return response()->json($allVotes);
    }

    public function wins(Number $number): JsonResponse
    {
        $wins = $number->wins()
            ->with(['winner', 'loser'])
            ->orderByDesc('created_at')
            ->simplePaginate(10);

        return response()->json($wins);
    }

    public function losses(Number $number): JsonResponse
    {
        $losses = $number->losses()
            ->with(['winner', 'loser'])
            ->orderByDesc('created_at')
            ->simplePaginate(10);

        return response()->json($losses);
    }
}
