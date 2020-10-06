<?php

declare(strict_types=1);

namespace VillaBrocante\GoogleShoppingFeed\Cron;

use VillaBrocante\GoogleShoppingFeed\Service\Feed as FeedService;

class CreateFeed
{
    protected FeedService $feedService;

    public function __construct(FeedService $feedService)
    {
        $this->feedService = $feedService;
    }

    public function __invoke(string $scope): void
    {
        $this->feedService->create();
    }
}
