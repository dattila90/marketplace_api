<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'parent_id',
    ];

    protected $casts = [
        'id' => 'string',
        'parent_id' => 'string',
    ];

    /**
     * Get the products for the category
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the parent category
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
