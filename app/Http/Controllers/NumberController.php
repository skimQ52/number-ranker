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
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NumberController extends Controller
{
    public function vote(Request $request, EloService $eloService, VoteRateLimiterService $rateLimiter): JsonResponse
    {
        $validated = $request->validate([
            'winner' => 'required|integer|between:1,99',
            'loser' => 'required|integer|between:1,99',
            'handshake' => 'required|string'
        ]);

        try {
            $data = Crypt::decrypt($request->input('handshake'));

            $nonceKey = 'handshake_nonce_' . $data['nonce'];
            if (Cache::has($nonceKey)) {
                return response()->json(['message' => 'Replay detected'], 403);
            }

            Cache::put($nonceKey, true, 300);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid handshake'], 403);
        }

        $submittedPair = collect([$validated['winner'], $validated['loser']])->sort()->values()->toArray();

        if ($data['pair'] != $submittedPair) {
            return response()->json(['message' => 'Handshake vote pair mismatch'], 403);
        }

        if ($validated['winner'] === $validated['loser']) {
            return response()->json(['message' => 'Invalid vote: numbers must differ'], 422);
        }

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
            'losses' => $loser->losses + 1,
        ]);

        $cacheKey = 'votes_' . Carbon::today('America/New_York')->toDateString();

        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, 1, Carbon::now('America/New_York')->endOfDay());
        } else {
            Cache::increment($cacheKey);
        }

        return $this->returnForNextVote($request);
    }

    public function duo(Request $request): JsonResponse
    {
        return $this->returnForNextVote($request);
    }

    public function index(): JsonResponse
    {
        $numbers = DB::table('numbers')->orderBy('elo', 'desc')->get();

        return response()->json($numbers);
    }

    public function returnForNextVote(Request $request): JsonResponse
    {

        $votesToday = Cache::get('votes_' . Carbon::today('America/New_York')->toDateString(), 0);
        $totalVotes = Number::query()->sum('wins');

        if ($request->boolean('meta_only')) {
            return response()->json([
                'votes' => $votesToday,
                'total' => $totalVotes,
            ]);
        }

        $duo = NumberService::duo();

        $handshakeData = [
            'pair' => $duo['pair'],
            'issued_at' => now()->timestamp,
            'nonce' => Str::random(8)
        ];

        $handshake = Crypt::encrypt($handshakeData);

        return response()->json([
            'left' => $duo['left'],
            'right' => $duo['right'],
            'votes' => $votesToday,
            'total' => $totalVotes,
            'handshake' => $handshake,
        ]);
    }
}
