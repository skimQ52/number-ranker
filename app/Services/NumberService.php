<?php

namespace App\Services;

use App\Models\Number;

class NumberService
{

    public static function duo(): array
    {
        $leftNumber = Number::query()->find(rand(1,100));

        $baseElo = $leftNumber->elo;
        $range = rand(1, 10) <= 7 ? 100 : 300;

        $rightNumber = Number::query()
            ->where('id', '!=', $leftNumber->id)
            ->whereBetween('elo', [$baseElo - $range, $baseElo + $range])
            ->inRandomOrder()
            ->first();

        if (!$rightNumber) {
            $rightNumber = Number::query()
                ->where('id', '!=', $leftNumber->id)
                ->inRandomOrder()
                ->first();
        }

        return array($leftNumber, $rightNumber);
    }
}
