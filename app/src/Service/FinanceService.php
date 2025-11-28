<?php

namespace App\Service;

class FinanceService
{
    public function getExchangeRate(): float
    {
        return 1.09; // Mock EUR -> USD
    }

    public function getBitcoinPrice(): float
    {
        return 39200.00; // Mock BTC Price
    }
}
