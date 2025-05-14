<?php

namespace App\Services;

class EloService
{
    protected int $kFactor; // Adjust the aggressiveness of elo changes

    public function __construct(int $kFactor = 16)
    {
        $this->kFactor = $kFactor;
    }

    public function win(float $playerRating, float $opponentRating): float
    {
        $expected = $this->expectedScore($playerRating, $opponentRating);
        return round($this->calculate($playerRating, $expected, 1));
    }

    public function loss(float $playerRating, float $opponentRating): float
    {
        $expected = $this->expectedScore($playerRating, $opponentRating);
        return round($this->calculate($playerRating, $expected, 0));
    }

    private function expectedScore(float $player, float $opponent): float
    {
        return 1 / (1 + pow(10, ($opponent - $player) / 400));
    }

    private function calculate(float $rating, float $expected, float $actual): float
    {
        return $rating + $this->kFactor * ($actual - $expected);
    }
}
