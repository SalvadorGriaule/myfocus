<?php
// src/Dto/Wind.php
namespace App\Dto;

final class Wind
{
    public function __construct(
        public readonly ?string $direction,
        public readonly ?int    $speed
    ) {}
}