<?php

// src/Dto/SevenTimerForecast.php
namespace App\Dto;

final class SevenTimerForecast
{
    public function __construct(
        public readonly string $product,
        public readonly string $init,
        /** @var DataSerie[] */
        public readonly array $dataseries
    ) {}
}
