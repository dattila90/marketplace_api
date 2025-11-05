<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seller extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'rating'
    ];

    protected $casts = [
        'rating' => 'decimal:2',
    ];

    /**
     * Get the products for the seller.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
