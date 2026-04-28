<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;

class CatalogController extends Controller
{
    /**
     * Generate Google Merchant Center XML Feed.
     */
    public function googleMerchantFeed()
    {
        $products = Product::where('status', 'active')
            ->orWhere('status', 'publish')
            ->get();
            
        $frontendUrl = rtrim(config('app.frontend_url'), '/');

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"></rss>');
        $channel = $xml->addChild('channel');
        
        $channel->addChild('title', config('app.name') . ' Product Catalog');
        $channel->addChild('link', $frontendUrl);
        $channel->addChild('description', 'Dynamic product feed for Google Merchant Center');

        foreach ($products as $product) {
            $item = $channel->addChild('item');
            
            // Basic Info
            $item->addChild('g:id', $product->sku ?: $product->id, 'http://base.google.com/ns/1.0');
            $item->addChild('g:title', htmlspecialchars($product->name), 'http://base.google.com/ns/1.0');
            $item->addChild('g:description', htmlspecialchars(strip_tags($product->description)), 'http://base.google.com/ns/1.0');
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
                $item->addChild('g:brand', htmlspecialchars($product->brand), 'http://base.google.com/ns/1.0');
            }
            if ($product->category) {
                // If it's a hierarchy, it should be like "Apparel & Accessories > Clothing"
                $item->addChild('g:google_product_category', htmlspecialchars($product->category), 'http://base.google.com/ns/1.0');
            }
        }

        return response($xml->asXML(), 200)
            ->header('Content-Type', 'application/xml');
    }
}
