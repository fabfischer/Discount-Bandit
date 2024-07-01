<?php

namespace App\Console\Commands;

use App\Classes\Stores\Amazon;
use App\Classes\URLHelper;
use App\Models\Product;
use Illuminate\Console\Command;

class SingleTestAmazonCrawl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discount:single-test-amazon-crawl {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to test the Amazon Crawl for a single product.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data = [];
        $url = $this->argument('url');
        $url = new URLHelper($url);
        $url->fill_data($data);

        $domain = parse_url($url->final_url)['host'];

        $product = Product::where('asin', $data['asin'])->first();

        if (!$product) {
            throw new \Exception('Product not found. Please add it first. This command is just for testing purposes.');
        }

        $stores = $product->stores();
        $product_store_id = null;
        foreach($stores->get() as $store) {
            if ($store->domain == $domain) {
                $product_store_id = $store->pivot->id;
                break;
            }
        }
        if (!$product_store_id) {
            throw new \Exception('no Product Store not found. Pleas add one. This command is just for testing purposes.');
        }

        $amazon = new Amazon($product_store_id);

        return 0;
    }
}
