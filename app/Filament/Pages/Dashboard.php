<?php

namespace App\Filament\Pages;

use App\Livewire\LatestPriceChanges;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;

class Dashboard extends \Filament\Pages\Dashboard
{
    // ...
    public function getWidgets(): array
    {
        return [
            LatestPriceChanges::class,
            AccountWidget::class,
            FilamentInfoWidget::class
        ];
    }



}
