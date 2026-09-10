<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'price_per_unit',
        'tax_percentage',
        'stock_on_hand',
    ];

    protected function casts(): array
    {
        return [
            'price_per_unit' => 'decimal:2',
            'tax_percentage' => 'decimal:2',
            'stock_on_hand' => 'integer',
        ];
    }

    /**
     * Senior-Level Modern PHP 8 Attribute Accessor.
     */
    protected function isLowStock(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->stock_on_hand <= (int) config('inventory.low_stock_threshold', 5)
        );
    }

    /**
     * Senior-Level Eloquent Local Query Scope.
     */
    public function scopeLowStock(Builder $query, ?int $threshold = null): Builder
    {
        $limit = $threshold ?? (int) config('inventory.low_stock_threshold', 5);

        return $query->where('stock_on_hand', '<=', $limit)
            ->orderBy('stock_on_hand', 'asc');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
