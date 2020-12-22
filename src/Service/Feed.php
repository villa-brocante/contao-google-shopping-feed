<?php

namespace VillaBrocante\GoogleShoppingFeed\Service;

use Contao\FilesModel;
use Contao\News;
use Contao\NewsModel;
use Contao\PageModel;
use Illuminate\Support\Collection;
use League\Flysystem\FilesystemInterface;
use Vitalybaev\GoogleMerchant\Feed as GoogleMerchantFeed;
use Vitalybaev\GoogleMerchant\Product;
use Vitalybaev\GoogleMerchant\Product\Availability\Availability;
use Vitalybaev\GoogleMerchant\Product\Shipping;

class Feed
{
    private FilesystemInterface $feedStorage;
    private GoogleMerchantFeed $googleMerchantFeed;
    private string $xmlFileName;

    public function __construct(FilesystemInterface $feedStorage, GoogleMerchantFeed $googleMerchantFeed, string $xmlFileName)
    {
        $this->feedStorage = $feedStorage;
        $this->googleMerchantFeed = $googleMerchantFeed;
        $this->xmlFileName = $xmlFileName;
    }

    public function create(): bool
    {
        $content = $this->googleMerchantFeed($this->googleMerchantFeed)->build();

        return $this->writeFeedFile($this->xmlFileName, $content);
    }

    public function getProductsCollection(): Collection
    {
        $products = NewsModel::findBy(
            ['use_in_gsf'],
            [1]
        );

        return collect($products);
    }

    public function googleMerchantFeed(GoogleMerchantFeed $feed): GoogleMerchantFeed
    {
        $this->getProductsCollection()->each(function (NewsModel $item, $key) use (&$feed) {
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
            $product->setAvailability($item->published ? Availability::IN_STOCK : Availability::OUT_OF_STOCK);

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
        return $this->feedStorage->put($file, $content);
    }
}
