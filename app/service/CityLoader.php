namespace App\Service;

class CityLoader
{
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function getCities(): array
    {
        $file = $this->projectDir . '/data/cities.json';
        if (!file_exists($file)) {
            return [];
        }

        $data = json_decode(file_get_contents($file), true);
        $cities = [];

        foreach ($data as $city) {
            $cities[$city['name']] = $city['name']; // ou $city['zipcode'] si tu veux
        }

        return $cities;
    }
}