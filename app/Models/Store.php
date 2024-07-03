<?php

namespace App\Models;

use App\Casts\Money;
use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Store extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'status'           => StatusEnum::class,
        'price'            => Money::class,
        'notify_price'     => Money::class,
        'shipping_price'   => Money::class,
        'pivot.updated_at' => 'datetime',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withTimestamps()->withPivot([
            "id",
            //data
            'price',
            'notify_price',
            'rate',
            'number_of_rates',
            'seller',
            'shipping_price',
            'updated_at',
            //extra settings
            'add_shipping',
            //ebay
            'remove_if_sold',
            'ebay_id',
        ]);
    }
}
