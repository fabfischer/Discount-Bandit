<?php

namespace App\Helper;

use App\Jobs\GetProductJob;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;

class QueueHelper
{
    public static function dispatchProductJob(
        null|ProductStore|Store|\stdClass $store = null,
        null|Product $product = null
    ): void {
        if (!$store) {
            return;
        }

        $queue = null;
        $param1 = null;
        $param2 = null;

        if ($store instanceof ProductStore || $store instanceof \stdClass) {
            $queue = (isset($store->slug)) ? $store->slug : $store->store->slug;
            $param1 = $store->id;
            $param2 = (isset($store->domain)) ? $store->domain : $store->store->domain;
        } elseif ($store instanceof Store && $product) {
            $queue = $store->slug;
            $param1 = $product->id;
            $param2 = $store->domain;
        }

        if (!$queue || !$param1 || !$param2) {
            return;
        }

        if (str_contains($queue, 'amazon')) {
            $queue = 'amazon';
        }
        GetProductJob::dispatch($param1, $param2)
            ->onQueue($queue)
            ->delay(now()->addSeconds(rand(1, 10) * 5));
    }
}
