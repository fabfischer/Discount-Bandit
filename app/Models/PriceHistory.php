<?php

namespace App\Models;

use App\Casts\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceHistory extends Model
{
    use HasFactory;

    protected $casts = [
        "price" => Money::class
    ];
    protected $guarded = ['id'];

    public function product_store(): BelongsTo
    {
        return $this->belongsTo(ProductStore::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    /**
     * Scope a query to only include the latest price history for each product.
     */
    public function scopeLatestPerProduct($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereIn('price_histories.id', function ($subquery) {
            $subquery->selectRaw('MAX(price_histories.id)') // Assuming 'id' is auto-incrementing
            ->from('price_histories')
                ->groupBy('product_id');
        });
    }
}
