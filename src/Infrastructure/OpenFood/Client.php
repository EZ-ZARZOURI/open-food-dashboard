<?php

// src/Infrastructure/OpenFood/Client.php
namespace App\Infrastructure\OpenFood;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class Client
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache
    ) {}

    public function getProducts(int $page = 1, int $pageSize = 10): array
    {
        $cacheKey = "openfood_products_{$page}_{$pageSize}";

        return $this->cache->get($cacheKey, function(ItemInterface $item) use ($page, $pageSize) {
            $item->expiresAfter(300); // cache 5 min

            try {
                $response = $this->httpClient->request(
                    'GET',
                    "https://world.openfoodfacts.org/cgi/search.pl",
                    [
                        'query' => [
                            'action' => 'process',
                            'json' => 'true',
                            'page_size' => $pageSize,
                            'page' => $page
                        ],
                        'headers' => [
                            'User-Agent' => 'SymfonyApp/1.0 (contact@example.com)'
                        ],
                        'timeout' => 50
                    ]
                );

                if ($response->getStatusCode() !== 200) {
                    throw new \RuntimeException('API OpenFoodFacts indisponible.');
                }

                $data = $response->toArray();
                return $data['products'] ?? [];

            } catch (\Exception $e) {
                // Retourne un tableau vide si erreur
                return [];
            }
        });
    }

    
    public function getTotalProducts(): int
    {
        $cacheKey = "openfood_total_products";

        return $this->cache->get($cacheKey, function(ItemInterface $item) {
            $item->expiresAfter(300); // cache 5 min

            try {
                $response = $this->httpClient->request(
                    'GET',
                    "https://world.openfoodfacts.org/cgi/search.pl",
                    [
                        'query' => [
                            'action' => 'process',
                            'json' => 'true',
                            'page_size' => 1, // juste 1 produit pour récupérer le count
                            'page' => 1
                        ],
                        'headers' => [
                            'User-Agent' => 'SymfonyApp/1.0 (contact@example.com)'
                        ],
                        'timeout' => 50
                    ]
                );

                $data = $response->toArray();

                // 'count' contient le nombre total de produits disponibles
                return $data['count'] ?? 0;

            } catch (\Exception $e) {
                return 0;
            }
        });
    }

}
