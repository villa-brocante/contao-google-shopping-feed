<?php

declare(strict_types=1);

namespace VillaBrocante\GoogleShoppingFeed\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\NewsBundle\ContaoNewsBundle;
use VillaBrocante\GoogleShoppingFeed\GoogleShoppingFeedBundle;

class Plugin implements BundlePluginInterface
{
    /**
     * @param ParserInterface $parser
     * @return array
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(GoogleShoppingFeedBundle::class)
                        ->setLoadAfter([
                            ContaoCoreBundle::class,
                            ContaoNewsBundle::class,
                        ]),
        ];
    }
}
