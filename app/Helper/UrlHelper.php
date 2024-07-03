<?php

namespace App\Helper;

use App\Classes\MainStore;
use App\Classes\Stores\Amazon;
use App\Classes\Stores\Ebay;
use App\Classes\Stores\Walmart;
use App\Models\Product;
use App\Models\Store;

class UrlHelper
{
    public static function generateUrl(?Product $product = null, ?Store $store = null): string
    {
        if (!$product || !$store) {
            return '#';
        }

        if (MainStore::is_amazon($store->domain)) {
            return Amazon::prepare_url($store->domain, $product->asin, Amazon::MAIN_URL, $store->referral);
        } elseif (MainStore::is_ebay($store->domain)) {
            return Ebay::prepare_url($store->domain, $store->ebay_id, Ebay::MAIN_URL, $store->referral);
        } elseif (MainStore::is_walmart($store->domain)) {
            return Walmart::prepare_url($store->domain, $product->walmart_ip, $store->referral);
        }

        return '#';
    }
}
