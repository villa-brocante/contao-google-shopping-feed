<?php

declare(strict_types=1);

namespace VillaBrocante\GoogleShoppingFeed\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ExtensionPluginInterface;
use Contao\NewsBundle\ContaoNewsBundle;
use League\FlysystemBundle\FlysystemBundle;
use VillaBrocante\GoogleShoppingFeed\GoogleShoppingFeedBundle;

class Plugin implements BundlePluginInterface, ExtensionPluginInterface
{
    /**
     * @param ParserInterface $parser
     * @return array
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(FlysystemBundle::class)
                ->setLoadAfter([
                    FrameworkBundle::class,
                ]),
            BundleConfig::create(GoogleShoppingFeedBundle::class)
                ->setLoadAfter([
                    ContaoCoreBundle::class,
                    ContaoNewsBundle::class,
                    FlysystemBundle::class,
                ]),
        ];
    }

    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container)
    {
        if ('flysystem' !== $extensionName) {
            return $extensionConfigs;
        }

        foreach ($extensionConfigs as &$extensionConfig) {
            if (isset($extensionConfig['storages'])) {
                continue;
            }

            $extensionConfig['storages'] = [
                'feed.storage' => [
                    'adapter' => 'local',
                    'options' => [
                        'directory' => '%kernel.project_dir%/web/files/feeds'
                    ],
                ],
            ];
        }

        return $extensionConfigs;
    }
}
