<?php

// src/Serializer/SevenTimerNormalizer.php
namespace App\Serializer;

use App\Dto\DataSerie;
use App\Dto\SevenTimerForecast;
use App\Dto\Wind;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class SevenTimerNormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): SevenTimerForecast
    {
        // 1. Si on reçoit une string, on la décode d'abord
        if (is_string($data)) {
            $decoded = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        } else {
            $decoded = $data;
        }

        // 2. Construction des objets
        $series = array_map(
            fn(array $s) => new DataSerie(
                timepoint: $s['timepoint'],
                cloudcover: $this->intOrNull($s['cloudcover'] ?? null),
                liftedIndex: $this->intOrNull($s['lifted_index'] ?? null),
                precType: $this->strOrNull($s['prec_type'] ?? null),
                precAmount: $this->intOrNull($s['prec_amount'] ?? null),
                temp2m: $this->intOrNull($s['temp2m'] ?? null),
                rh2m: $this->strOrNull($s['rh2m'] ?? null),
                wind10m: new Wind(
                    direction: $this->windDir($s['wind10m']['direction'] ?? null),
                    speed: $this->intOrNull($s['wind10m']['speed'] ?? null)
                ),
                weather: $s['weather']
            ),
            $decoded['dataseries'] ?? []
        );

        return new SevenTimerForecast(
            product: $decoded['product'],
            init: $decoded['init'],
            dataseries: $series
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === SevenTimerForecast::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        // optimisation : on indique que ce normalizer gère SevenTimerForecast
        return [
            SevenTimerForecast::class => true,
        ];
    }

    /* ---------- helpers ---------- */
    private function intOrNull(mixed $v): ?int
    {
        if ($v === '' || $v === null || $v === -9999) {
            return null;
        }
        return (int) $v;
    }

    private function strOrNull(mixed $v): ?string
    {
        if ($v === '' || $v === null) {
            return null;
        }
        return (string) $v;
    }

    private function windDir(mixed $v): ?string
    {
        if ($v === '-9999' || $v === '') {
            return null;
        }
        return $v ? (string) $v : null;
    }
}
