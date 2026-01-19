<?php

// src/Application/Product/GetProductsUseCase.php
namespace App\Application\Product;

use App\Infrastructure\OpenFood\Client;
use App\Domain\Product\Product;

class GetProductsUseCase
{
    public function __construct(private Client $client) {}

    /**
     * @return Product[]
     */
    public function execute(int $page, int $pageSize): array
    {
        $rawProducts = $this->client->getProducts($page, $pageSize);

        return array_map(fn($p) => new Product(
            $p['product_name'] ?? 'Nom inconnu',
            $p['brands'] ?? 'Marque inconnue',
            $p['code'] ?? 'Code inconnu',
            $p['countries'] ?? 'Pays inconnu',
            $p['categories'] ?? 'Catégorie inconnue',
            $p['image_url'] ?? null
        ), $rawProducts);
    }
    public function count(): int
    {
        return $this->client->getTotalProducts(); // <- on va créer cette méthode dans le Client
    }


}
