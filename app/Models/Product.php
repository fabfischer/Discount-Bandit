<?php

namespace App\Models;

use App\Casts\Money;
use App\Enums\StatusEnum;
use App\Helper\UrlHelper;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $appends = ['url'];
    protected $casts = [
        'status'                      => StatusEnum::class,
        'stores.pivot.price'          => Money::class,
        'stores.pivot.notify_price'   => Money::class,
        'stores.pivot.shipping_price' => Money::class,
        'stores.pivot.updated_at'     => 'datetime',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class)->withTimestamps()->withPivot([
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

    public function variations(): HasMany
    {
        return $this->hasMany(Product::class);
    }


    public function parent_variation(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id', 'product_id');
    }

    public function product_store(): HasMany
    {
        return $this->hasMany(ProductStore::class, 'product_id', 'id');
    }

    public function stores_available(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id')->whereHas('products');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)->withTimestamps()->withPivot("key");
    }

    public function price_history(): HasMany
    {
        return $this->hasMany(PriceHistory::class, 'product_id', 'id');
    }

    public function last_price_history(): HasMany
    {
        return $this->hasMany(PriceHistory::class, 'product_id', 'id')
            ->orderBy('created_at', 'desc')
            ->limit(5);
    }

    protected function url(): Attribute
    {
        return Attribute::make(
            get: function(mixed $value, array $attributes) {
                $record = Product::find($attributes['id']);
                // get first store
                $productStore = $record->product_store->first();
                if (!$productStore) {
                    return null;
                }
                $store = $productStore->store;
                if (!$store) {
                    return null;
                }

                return UrlHelper::generateUrl(
                    $record,
                    $store
                );
            },
        );
    }
}
