<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CatalogController extends Controller
{
    /**
     * Generate Facebook Catalog XML feed.
     * 
     * @return \Illuminate\Http\Response
     */
    public function facebookCatalog()
    {
        $products = Product::whereIn('status', ['active', 'publish', 'published'])->get();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss xmlns:g="http://base.google.com/ns/1.0" version="2.0"/>');
        $channel = $xml->addChild('channel');
        $channel->addChild('title', 'Lux Shop Product Catalog');
        $channel->addChild('link', config('app.url'));
        $channel->addChild('description', 'Luxury products curated for you.');

        foreach ($products as $product) {
            $item = $channel->addChild('item');
            
            // Required Facebook Fields
            $item->addChild('g:id', $product->id, 'http://base.google.com/ns/1.0');
            $item->addChild('g:title', $this->cleanString($product->name), 'http://base.google.com/ns/1.0');
            $item->addChild('g:description', $this->cleanString($product->short_description ?: $product->description), 'http://base.google.com/ns/1.0');
            $item->addChild('g:link', config('app.frontend_url', 'https://lux.nayemuf.com') . '/shop/' . $product->slug, 'http://base.google.com/ns/1.0');
            $item->addChild('g:image_link', $product->image_url, 'http://base.google.com/ns/1.0');
            $item->addChild('g:brand', $this->cleanString($product->brand ?: 'Lux'), 'http://base.google.com/ns/1.0');
            $item->addChild('g:condition', 'new', 'http://base.google.com/ns/1.0');
            $item->addChild('g:availability', $product->in_stock ? 'in stock' : 'out of stock', 'http://base.google.com/ns/1.0');
            
            // Price (Format: 100.00 BDT)
            $price = $product->sellable_price ?: $product->price;
            $item->addChild('g:price', number_format($price, 2, '.', '') . ' BDT', 'http://base.google.com/ns/1.0');
            
            // Optional but recommended
            if ($product->category) {
                $item->addChild('g:google_product_category', $this->cleanString($product->category), 'http://base.google.com/ns/1.0');
                $item->addChild('g:product_type', $this->cleanString($product->category), 'http://base.google.com/ns/1.0');
            }
            
            if ($product->sku) {
                $item->addChild('g:mpn', $product->sku, 'http://base.google.com/ns/1.0');
            }
        }

        return Response::make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Generate Google Merchant Center XML Feed.
     * 
     * @return \Illuminate\Http\Response
     */
    public function googleMerchantFeed()
    {
        $products = Product::whereIn('status', ['active', 'publish', 'published'])->get();
            
        $frontendUrl = rtrim(config('app.frontend_url'), '/');

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"></rss>');
        $channel = $xml->addChild('channel');
        
        $channel->addChild('title', config('app.name', 'Lux') . ' Product Catalog');
        $channel->addChild('link', $frontendUrl);
        $channel->addChild('description', 'Dynamic product feed for Google Merchant Center');

        foreach ($products as $product) {
            $item = $channel->addChild('item');
            
            // Basic Info
            $item->addChild('g:id', $product->sku ?: $product->id, 'http://base.google.com/ns/1.0');
            $item->addChild('g:title', $this->cleanString($product->name), 'http://base.google.com/ns/1.0');
            $item->addChild('g:description', $this->cleanString($product->description), 'http://base.google.com/ns/1.0');
            $item->addChild('g:link', $frontendUrl . '/shop/' . $product->slug, 'http://base.google.com/ns/1.0');
            $item->addChild('g:image_link', $product->image_url, 'http://base.google.com/ns/1.0');
            
            // Availability & Condition
            $item->addChild('g:availability', $product->in_stock ? 'in stock' : 'out of stock', 'http://base.google.com/ns/1.0');
            $item->addChild('g:condition', 'new', 'http://base.google.com/ns/1.0');
            
            // Pricing (Google requires currency code, e.g., "100.00 BDT")
            $price = $product->sellable_price ?: $product->price;
            $item->addChild('g:price', number_format($price, 2, '.', '') . ' BDT', 'http://base.google.com/ns/1.0');
            
            // Optional but recommended
            if ($product->brand) {
                $item->addChild('g:brand', $this->cleanString($product->brand), 'http://base.google.com/ns/1.0');
            }
            if ($product->category) {
                $item->addChild('g:google_product_category', $this->cleanString($product->category), 'http://base.google.com/ns/1.0');
            }
        }

        return Response::make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Clean string for XML output.
     * 
     * @param string $string
     * @return string
     */
    private function cleanString($string)
    {
        if (!$string) return '';
        return htmlspecialchars(strip_tags($string), ENT_XML1, 'UTF-8');
    }
}
