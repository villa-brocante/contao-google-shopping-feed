<?php

namespace VillaBrocante\GoogleShoppingFeed\Tests\Service;

use VillaBrocante\GoogleShoppingFeed\Service\Feed;
use VillaBrocante\GoogleShoppingFeed\Tests\TestCase;

class FeedTest extends TestCase
{
    public function testItCanInstantiate()
    {
        $webDir = $this->getContainerWithContaoConfiguration()->getParameter('contao.web_dir');
        $instance = new Feed($webDir);

        $this->assertInstanceOf(Feed::class, $instance);
    }
}
