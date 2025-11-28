<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

class CityLoader
{
    private string $projectDir;

    public function __construct(ParameterBagInterface $params)
    {
        $this->projectDir = $params->get('kernel.project_dir');
    }

    public function getCities(LoggerInterface $logger): array
    {
        $file = $this->projectDir . '/data/france.json';
        if (!file_exists($file)) {
            return [];
        }
        $data = json_decode(file_get_contents($file), true);
        $logger->info($data);
        $cities = [];

        foreach ($data as $city) {
            $cities[$city['Nom_commune']] = $city['Nom_commune']; // ou $city['zipcode'] si tu veux
        }

        return $cities;
    }
}