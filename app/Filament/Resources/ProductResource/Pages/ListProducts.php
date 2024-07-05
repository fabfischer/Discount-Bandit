<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Livewire\LatestPriceChanges;
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

    protected function getHeaderWidgets(): array
    {
        return [
            LatestPriceChanges::class,
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
                $query->whereHas('categories', function ($query) use ($category) {
                    $query->where([
                        'categories.id' => $category->id,
                    ]);
                });
            }));
        }
        return $tabs;
    }

    public function getTableQueryForExport(): Builder
    {
        // TODO: Implement getTableQueryForExport() method.
        return $this->getTableQuery();
    }
}
