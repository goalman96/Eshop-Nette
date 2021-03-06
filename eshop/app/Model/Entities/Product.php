<?php

namespace App\Model\Entities;

use LeanMapper\Entity;

/**
 * class Product
 * @package App\Model\Entities
 * @property int $productId
 * @property string $title
 * @property string $description
 * @property string $url
 * @property string $photoExtension = ''
 * @property float $price
 * @property bool $available = true
 * @property Category|null $category m:hasOne
 */

class Product extends Entity
{

}