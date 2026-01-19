<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProductService
{
    public function __construct(
        private HttpClientInterface $httpClient
    ) {}

    public function getProducts(
        ?string $filterType = null,
        ?string $filterValue = null,
        int $page = 1,
        int $pageSize = 6
    ): array {
        $url = "https://world.openfoodfacts.org/cgi/search.pl";
        $url .= "?action=process&json=true";
        $url .= "&page_size={$pageSize}&page={$page}";

        if ($filterType && $filterValue) {
            $url .= "&tagtype_0={$filterType}&tag_0={$filterValue}";
        }

        $response = $this->httpClient->request('GET', $url);

        if ($response->getStatusCode() !== 200) {
            return [];
        }

        $data = $response->toArray();
        $products = $data['products'] ?? [];

        return array_map(fn ($p) => [
            'name'       => $p['product_name'] ?? null,
            'brand'      => $p['brands'] ?? null,
            'code'       => $p['code'] ?? null,
            'countries'  => $p['countries'] ?? null,
            'categories' => $p['categories'] ?? null,
            'imageUrl'   => $p['image_front_url'] ?? null,
        ], $products);
    }
}
