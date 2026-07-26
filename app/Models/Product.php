<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'category',
        'unit',
        'stock',
        'minimum_stock',
        'price',
        'location',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
