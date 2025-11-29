<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class FinanceService
{

    public function __construct(
        private readonly HttpClientInterface $coinGeckoClient,
        private readonly HttpClientInterface $exchangeClient // client nommé
    ) {}

    public function getEurToUsdRate(): float
    {
        $response = $this->exchangeClient->request(
            'GET',
            'latest',
            [
                'query' => [
                    'base'    => 'EUR',
                    'symbols' => 'USD',        // <─ bien présent
                ],
            ]
        );

        // 1. strictement 2xx
        if ($response->getStatusCode() >= 300) {
            throw new \RuntimeException('ExchangeRate.host HTTP ' . $response->getStatusCode());
        }

        $data = $response->toArray();
        dump($data); // debug rapide

        if (!isset($data['rates']['USD'])) {
            throw new \RuntimeException('Clé USD manquante dans la réponse : ' . json_encode($data));
        }

        return $data['rates']['USD'];
    }
    /**
     * Retourne le prix actuel du Bitcoin en EUR.
     * @throws \Exception si l'appel échoue
     */
    public function getBitcoinPriceEur(): float
    {
        $response = $this->coinGeckoClient->request(
            'GET',
            'api/v3/simple/price',
            [
                'query' => [
                    'ids'           => 'bitcoin',
                    'vs_currencies' => 'eur',
                ],
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('CoinGecko indisponible');
        }

        $data = $response->toArray(); // ["bitcoin" => ["eur" => 26_150.42]]
        if (!isset($data['bitcoin']['eur'])) {
            throw new \RuntimeException('Clé de réponse inattendue');
        }

        return $data['bitcoin']['eur'];
    }
}
