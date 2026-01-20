<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProductService
{
    public function __construct(
        private HttpClientInterface $httpClient
    ) {}

    /**
     * Récupère tous les produits liés à un widget
     */
    public function getProductsByWidget(
        ?string $filterType = null,
        ?string $filterValue = null
    ): array {
        $url = '';
        $limit = 10; // limite de sécurité

        switch ($filterType) {
            case 'categorie':
                $url = "https://world.openfoodfacts.org/category/{$filterValue}.json?page_size={$limit}";
                break;

            case 'marque':
                $url = "https://world.openfoodfacts.org/brand/{$filterValue}.json?page_size={$limit}";
                break;
        }

        if ($url === '') {
            return [];
        }

        $response = $this->httpClient->request('GET', $url);

        if ($response->getStatusCode() !== 200) {
            return [];
        }

        $data = $response->toArray();
        $products = $data['products'] ?? [];

        return array_map(fn ($p) => [
            'name'       => $p['product_name'] ?? 'Nom inconnu',
            'brand'      => $p['brands'] ?? 'Marque inconnue',
            'code'       => $p['code'] ?? 'Code inconnu',
            'countries'  => $p['countries'] ?? 'Pays inconnu',
            'categories' => $p['categories'] ?? 'Catégorie inconnue',
            'imageUrl'   => $p['image_front_url'] ?? 'https://placehold.co/200x200?text=No+Image',
        ], $products);
    }
}
