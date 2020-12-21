<?php

namespace VillaBrocante\GoogleShoppingFeed\Service;

use Contao\FilesModel;
use Contao\News;
use Contao\NewsModel;
use Contao\PageModel;
use League\Flysystem\FilesystemInterface;
use Vitalybaev\GoogleMerchant\Feed as GoogleShoppingFeed;
use Vitalybaev\GoogleMerchant\Product;
use Vitalybaev\GoogleMerchant\Product\Availability\Availability;
use Vitalybaev\GoogleMerchant\Product\Shipping;

class Feed
{
    private FilesystemInterface $feedStorage;

    public function __construct(FilesystemInterface $defaultStorage)
    {
        $this->feedStorage = $defaultStorage;
    }

    public function create(string $path = 'google_shopping_feed.xml'): bool
    {
        $feed = $this->createFeed($this->getBrand());
        $xml = $this->getProducts($feed)->build();

        return $this->writeFeedFile($path, $xml);
    }

    public function createFeed(string $brand): GoogleShoppingFeed
    {
        return new GoogleShoppingFeed(
            $brand,
            'https://www.villa-brocante.de',
            'Individuelle Echtholz / Massivholzmöbel nach Maß: Küchentische & Esstische · Kommoden · Couchtische · Designerstücke · Stühle · Holzfliegen / Woodfly'
        );
    }

    public function getProducts(GoogleShoppingFeed $feed): GoogleShoppingFeed
    {
        $products = collect(NewsModel::findBy('use_in_gsf', 1));

        $products->each(function (NewsModel $item, $key) use (&$feed) {
            $product = new Product();
            $product->setId($item->id);
            $product->setTitle($item->headline);
            $product->setPrice($this->extractPrice($item->teaser));
            $product->setDescription($item->description);

            if (!empty($item->singleSRC)) {
                $product->setImage($this->getBaseUrl() . '/' . FilesModel::findByUuid($item->singleSRC)->path);
            }

            if (!empty($item->gsf_gtin)) {
                $product->setGtin($item->gsf_gtin);
            }

            if (!empty($item->gsf_mpn)) {
                $product->setMpn($item->gsf_mpn);
            }

            $product->setBrand($this->getBrand());
            $product->setLink(News::generateNewsUrl($item));
            $product->setAvailability(Availability::IN_STOCK);

            if (!empty($item->gsf_shipping_costs)) {
                $shipping = new Shipping();
                $shipping->setCountry('DE');
                $shipping->setPrice($item->gsf_shipping_costs . ' €');
                $product->setShipping($shipping);
            }

            $feed->addProduct($product);
        });

        return $feed;
    }

    private function getBaseUrl(): string
    {
        $root = PageModel::findPublishedRootPages();

        $scheme = $root->rootUseSSL ? 'https://' : 'http://';
        $host = $root->dns;

        return $scheme . $host;
    }

    private function getBrand(): string
    {
        $root = PageModel::findPublishedRootPages();

        return $root->title;
    }

    private function extractPrice(string $text, string $currency = '€'): string
    {
        preg_match('/price">(.*)€/', $text, $matches);
        $price = '';

        if (isset($matches[1]) && !empty($matches[1])) {
            $price = trim($matches[1]) . ' ' . $currency;
        }

        return $price;
    }

    public function writeFeedFile(string $file, string $content): bool
    {
        return $this->feedStorage->write($file, $content);
    }
}
