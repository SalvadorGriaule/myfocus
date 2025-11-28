<?php
// src/Controller/MeteoController.php
namespace App\Controller;

use App\Service\CityLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MeteoController extends AbstractController
{
    public function __construct(
        private readonly CityLoader      $cityLoader,
        private readonly HttpClientInterface $httpClient,
    ) {}

    #[Route('/api/meteo', name: 'api_meteo', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $nomVille = $request->query->get('ville');
        if (!$nomVille) {
            return new JsonResponse(['error' => 'Paramètre « ville » manquant'], 400);
        }

        // 1. récupère les coordonnées
        $villes = $this->cityLoader->getCitiesWithCoords(); // tableau [ ['Nom_commune'=>..., 'coordonnees_gps'=>'lat,lon'], ... ]
        $ville = array_values(array_filter($villes, fn($v) => strcasecmp($v['Nom_commune'], $nomVille) === 0))[0] ?? null;
        if (!$ville) {
            return new JsonResponse(['error' => 'Ville inconnue'], 404);
        }

        [$lat, $lon] = explode(', ', $ville['coordonnees_gps']);

        // 2. appel 7Timer!
        $url = sprintf(
            'http://www.7timer.info/bin/api.pl?lon=%s&lat=%s&product=civil&output=json',
            $lon, $lat
        );
        $res = $this->httpClient->request('GET', $url);
        $data = $res->toArray();

        // 3. extraction de la première tranche (0 = maintenant)
        $current = $data['dataseries'][0];

        return new JsonResponse([
            'ville'       => $ville['Nom_commune'],
            'temperature' => $current['temp2m'],   // °C
            'temps'       => $this->translateWeather($current['weather']),
            'vent'        => $current['wind10m']['speed'], // km/h
        ]);
    }

    // correspondance rapide entre code 7Timer! et libellé simple
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