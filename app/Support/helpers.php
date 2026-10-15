<?php

use App\Support\Money;

if (! function_exists('money')) {
    function money(int $pence): string
    {
        return Money::format($pence);
    }
}
