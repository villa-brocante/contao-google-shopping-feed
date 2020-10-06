<?php

declare(strict_types=1);

namespace VillaBrocante\GoogleShoppingFeed\Model;

use Illuminate\Support\Collection;

class Feed
{
    public string $title;
    public string $link;
    public string $description;

    public array $items;
}
