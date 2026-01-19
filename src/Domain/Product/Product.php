<?php

// src/Domain/Product/Product.php
namespace App\Domain\Product;

class Product
{
    public function __construct(
        public string $name,
        public string $brand,
        public string $code,
        public string $countries,
        public string $categories,
        public ?string $imageUrl
    ) {}
}
