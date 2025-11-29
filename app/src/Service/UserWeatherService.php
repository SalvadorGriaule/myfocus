<?php
// src/Service/UserWeatherService.php
namespace App\Service;

use App\Dto\SevenTimerForecast;
use App\Repository\UserRepository;
use App\Serializer\SevenTimerNormalizer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UserWeatherService
{
    public function __construct(
        private readonly CityLoader           $cityLoader,
        private readonly HttpClientInterface $httpClient,
        private readonly UserRepository      $userRepository,
        private readonly SevenTimerNormalizer $normalizer,
    ) {}

    public function getCurrentForUser(int $userId): array
    {
        $user = $this->userRepository->find($userId);
        if (!$user || !$user->getCity()) {
            return [];
        }

        $cityName = $user->getCity();
        $villes   = $this->cityLoader->getCitiesWithCoords();
        $ville    = array_values(
            array_filter($villes, fn($v) => strcasecmp($v['Nom_commune'], $cityName) === 0)
        )[0] ?? null;

        if (!$ville) {
            throw new \RuntimeException("Ville « {$cityName} » inconnue dans notre base.");
        }

        [$lat, $lon] = explode(', ', $ville['coordonnees_gps']);
        $url         = "http://www.7timer.info/bin/api.pl?lon={$lon}&lat={$lat}&product=civil&output=json";

        $response = $this->httpClient->request('GET', $url);
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('7Timer! HTTP '.$response->getStatusCode());
        }

        // 1. Nettoyage
        $raw = ltrim($response->getContent(), "# \t\n\r");

        // 2. Remplacer les valeurs invalides par null JSON
        $raw = preg_replace('/"temp2m"\s*:\s*,/', '"temp2m":null,', $raw);
        $raw = preg_replace('/"rh2m"\s*:\s*""/', '"rh2m":null', $raw);
        $raw = preg_replace('/"temp2m"\s*:\s*""/', '"temp2m":null', $raw);

        // 3. Décoder
        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Réponse 7Timer! invalide : ' . $raw, 0, $e);
        }

        // 4. Normalizer
        try {
            $forecast = $this->normalizer->denormalize($data, SevenTimerForecast::class);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Normalizer error : ' . $e->getMessage(), 0, $e);
        }

        $current = $forecast->dataseries[0] ?? throw new \RuntimeException('Aucune tranche météo');

        return [
            'ville'       => $ville['Nom_commune'],
            'temperature' => $current->temp2m,
            'temps'       => $this->translateWeather($current->weather),
            'vent'        => $current->wind10m->speed,
        ];
    }

    private function translateWeather(string $code): string
    {
        return match ($code) {
            'clear'        => 'clear',
            'pcloudy'      => 'partly cloudy',
            'mcloudy'      => 'mostly cloudy',
            'cloudy'       => 'cloudy',
            'humid'        => 'humid',
            'lightrain'    => 'light rain',
            'oshower'      => 'occasional showers',
            'ishower'      => 'isolated showers',
            'lightsnow'    => 'light snow',
            'rain'         => 'rain',
            'snow'         => 'snow',
            'rainsnow'     => 'rain & snow',
            default        => $code,
        };
    }
}