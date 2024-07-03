<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Category;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make(),
        ];

        $categories = Category::all();
        foreach ($categories as $category) {
            $tabs = Arr::add($tabs, $category->name, Tab::make()->modifyQueryUsing(function (Builder $query) use ($category) {
                $query->join('category_product', 'category_product.product_id', '=', 'products.id')
                    ->where('category_product.category_id', $category->id);
            }));
        }


        /*$stores = Cache::get('stores_available');
        if (!$stores) {
            $stores = Store::whereNotIn('status', StatusEnum::ignored())
                ->where('tabs', true)->get();
            Cache::set('stores_available', $stores, 60 * 60 * 24);
        }

        foreach ($stores as $store) {
            $tabs = Arr::add($tabs, $store->name,
                ListRecords\Tab::make()->modifyQueryUsing(function (Builder $query) use ($store) {
                    $query->whereHas('stores', function ($query) use ($store) {
                        $query->where([
                            'stores.id' => $store->id,
                        ]);
                    });
                })
            );
        }*/
        return $tabs;
    }

    public function getTableQueryForExport(): Builder
    {
        // TODO: Implement getTableQueryForExport() method.
    }
}
