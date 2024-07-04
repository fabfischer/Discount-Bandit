<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Widgets\PriceHistoryChart;
use App\Helper\QueueHelper;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Log;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('Fetch')->color('primary')->action(function ($record) {
                try {
                    $stores = $record->stores;
                    foreach ($stores as $store) {
                        QueueHelper::dispatchProductJob($store?->getModel(), $record);
                        /*GetProductJob::dispatch(
                            $record->id,
                            $store->id,
                            $store->currency->code,
                            $store->pivot->notify_price,
                            $store->pivot->price)
                            ->delay(Carbon::now()->addSeconds(10));*/
                    }

                    Notification::make()
                        ->title('Added To Fetching Jobs')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Log::error("Couldn't fetch the job with error : $e" . json_encode($record));
                    Notification::make()
                        ->title("Couldn't fetch the product, refer to logs")
                        ->danger()
                        ->send();
                }
            }),
        ];
    }

    protected function getFooterWidgets(): array
    {
        if ($this->getRecord()->stores()->count()) {
            return [
                PriceHistoryChart::class,
            ];
        }
        return [];
    }

    public function getTitle(): string
    {
        return $this->record && $this->record?->name ? $this->record?->name : 'View Product';
    }
}
