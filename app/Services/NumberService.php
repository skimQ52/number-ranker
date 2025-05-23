<?php

namespace App\Services;

use App\Models\Number;

class NumberService
{

    public static function duo(): array
    {
        $randomLeft = rand(1,100);
        $randomRight = rand(1,100);

        while($randomLeft === $randomRight) {
            $randomRight = rand(1,100);
        }

        $leftNumber = Number::query()->find($randomLeft);
        $rightNumber = Number::query()->find($randomRight);

        return array($leftNumber, $rightNumber);
    }
}
