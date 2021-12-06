<?php

namespace App\Model\Facades;

use App\Model\Repositories\ProductRepository;

class ProductsFacade {

    /** @var ProductRepository $productRepository */
    private $productRepository;

    public function __construct(ProductRepository $productRepository) {
        $this->productRepository = $productRepository;
    }


}