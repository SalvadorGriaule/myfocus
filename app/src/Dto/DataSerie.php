<?php

// src/Dto/DataSerie.php
namespace App\Dto;

final class DataSerie
{
    public function __construct(
        public readonly int    $timepoint,
        public readonly ?int   $cloudcover,
        public readonly ?int   $liftedIndex,
        public readonly ?string $precType,
        public readonly ?int   $precAmount,
        public readonly ?int   $temp2m,
        public readonly ?string $rh2m,
        public readonly Wind   $wind10m,
        public readonly string $weather
    ) {}
}