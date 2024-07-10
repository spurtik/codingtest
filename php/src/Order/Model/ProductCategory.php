<?php

declare(strict_types=1);

namespace Catch\TradeEngineering\Order\Model;
class ProductCategory
{

    public const CATEGORY_PRODUCT_ESSENTIALS = 'Essentials';

    public const CATEGORY_PRODUCT_LUXURY = 'Luxury';

    public function __construct(private string $name, private string $category)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCategory(): string
    {
        return $this->category;
    }
}