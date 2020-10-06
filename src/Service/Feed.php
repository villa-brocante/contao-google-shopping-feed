<?php

namespace VillaBrocante\GoogleShoppingFeed\Service;

use Contao\Environment;
use Contao\File;
use Contao\FilesModel;
use Contao\News;
use Contao\NewsModel;
use Contao\PageModel;
use Vitalybaev\GoogleMerchant\Feed as GoogleShoppingFeed;
use Vitalybaev\GoogleMerchant\Product;

class Feed
{
    const GSF_DIR = '/files/feeds';

    public function create(string $path = 'google_shopping_feed.xml'): void
    {
        $feed = new GoogleShoppingFeed(
            'Villa Brocante',
            'https://www.villa-brocante.de',
            'Individuelle Echtholz / Massivholzmöbel nach Maß: Küchentische & Esstische · Kommoden · Couchtische · Designerstücke · Stühle · Holzfliegen / Woodfly'
        );

        $xml = $this->getProducts($feed)->build();

        File::putContent(self::GSF_DIR . '/' . $path, $xml);
    }

    protected function getProducts(GoogleShoppingFeed $feed): GoogleShoppingFeed
    {
        $products = collect(NewsModel::findBy('use_in_gsf', 1));

        $products->each(function (NewsModel $item, $key) use (&$feed) {
            $product = new Product();
            $product->setId($item->id);
            $product->setTitle($item->headline);
            $product->setPrice($this->extractPrice($item->teaser));
            $product->setDescription($item->description);

            if(!empty($item->singleSRC)) {
                $product->setImage('https://www.villa-brocante.de/' . FilesModel::findByUuid($item->singleSRC)->path);
            }

            if(!empty($item->gsf_gtin)) {
                $product->setGtin($item->gsf_gtin);
            }

            $product->setMpn($item->gsf_mpn);
            $product->setLink(News::generateNewsUrl($item));

            $feed->addProduct($product);
        });

        return $feed;
    }

    private function extractPrice(string $text, string $currency = '€'): string
    {
        preg_match('/price">(.*)€/', $text, $matches);
        $price = '';

        if(isset($matches[1]) && !empty($matches[1])) {
            $price = trim($matches[1]) . ' ' . $currency;
        }

        return $price;
    }
}
