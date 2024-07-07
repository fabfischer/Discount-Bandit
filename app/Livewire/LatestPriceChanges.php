<?php

namespace App\Livewire;

use App\Filament\Resources\ProductResource\Pages\ViewProduct;
use App\Helper\UrlHelper;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Store;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class LatestPriceChanges extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $sql = 'SELECT 
    ph.id,
    ph.price,
    p.name,
    ph.change,
    ph.created_at
FROM
    `price_histories` AS ph
    INNER JOIN (
        SELECT 
            `product_id`, 
            MAX(`created_at`) AS latest
        FROM 
            `price_histories`
        GROUP BY 
            `product_id`
    ) AS latest_ph ON ph.product_id = latest_ph.product_id AND ph.created_at = latest_ph.latest
    INNER JOIN `products` AS p ON ph.product_id = p.id
ORDER BY
    ph.id DESC
LIMIT 20;';

        /*$query = PriceHistory::query()
            ->latestPerProduct() // Use the defined scope
            ->join('products', 'price_histories.product_id', '=', 'products.id')
            ->select('price_histories.*', 'products.name as product_name') // Adjust the select as needed
            ->orderBy('price_histories.id', 'DESC')
            ->limit(20);*/

        /*$query = PriceHistory::query()
            ->select('price_histories.*', 'products.name as product_name')
            ->join('products', 'price_histories.product_id', '=', 'products.id')
            ->joinSub(function ($subquery) {
                $subquery->from('price_histories')
                    ->selectRaw('MAX(id) as id')
                    ->groupBy('product_id');
            }, 'latest_ph', function ($join) {
                $join->on('price_histories.id', '=', 'latest_ph.id');
            })
            ->orderBy('price_histories.id', 'DESC')
            ->limit(20);*/

        // Subquery to get the latest record for each product
        $latestPriceHistoriesSubquery = PriceHistory::selectRaw('MAX(created_at) as latest, product_id')
            ->groupBy('product_id');

        // Main query that joins the subquery to get the latest price history details
        // and applies the limit to restrict the number of results
        // TODO: limit is not working ... ????
        $query = PriceHistory::query()
            ->joinSub($latestPriceHistoriesSubquery->toSql(), 'latest_ph', function ($join) {
                $join->on('price_histories.product_id', '=', 'latest_ph.product_id')
                    ->on('price_histories.created_at', '=', 'latest_ph.latest');
            })
            ->join('products', 'price_histories.product_id', '=', 'products.id')
            ->join('product_store', 'product_store.product_id', '=', 'products.id') // TODO: take store into account
            ->select('price_histories.*', 'products.name as product_name', 'product_store.best_price')
            ->mergeBindings($latestPriceHistoriesSubquery->getQuery()) // Important: merge SQL bindings
            ->orderBy('price_histories.created_at', 'DESC')
            ->limit(20);

        // dump($query->getQuery()->dumpRawSql()); die('---end---');

        return $table
            ->searchable(false)
            ->heading('Latest Price Changes')
            ->paginationPageOptions([10, 'all'])
            ->query(
                $query
            )
            ->recordUrl(
                function (Model $record): string {
                    return ViewProduct::getUrl([$record->product_id]);
                }
            )
            ->columns([
                TextColumn::make('product_name')
                    ->limit(50)
                    ->label('Product Name')
                    ->searchable()

                    ->sortable(),
                TextColumn::make('change')
                    ->label('Last Price Change'),
                TextColumn::make('price')
                    ->label('Current Price')
                    ->color(fn($record) => (($record->price <= $record->notify_price) ? "success" : "danger")),
                TextColumn::make('best_price')
                    ->label('Best Price (in History)'),
            ])
            ->actions([]);
    }
}
