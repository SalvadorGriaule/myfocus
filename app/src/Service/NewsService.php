<?php

namespace App\Service;

class NewsService
{
    public function getNews(string $keywords): array
    {
        // Mock data
        return [
            [
                'title' => 'Symfony 7.0 Released',
                'source' => 'Symfony Blog',
                'url' => 'https://symfony.com/blog',
            ],
            [
                'title' => 'PHP 8.3 Features',
                'source' => 'PHP.net',
                'url' => 'https://www.php.net',
            ],
            [
                'title' => 'Tech Trends 2024',
                'source' => 'TechCrunch',
                'url' => 'https://techcrunch.com',
            ],
        ];
    }
}
