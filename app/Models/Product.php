<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
  use HasFactory, HasUuids;

  protected $fillable = [
    'title',
    'brand',
    'category_id',
    'price',
    'currency',
    'stock',
    'seller_id',
    'rating',
    'popularity',
    'attributes',
  ];

  protected $casts = [
    'id' => 'string',
    'category_id' => 'string',
    'seller_id' => 'string',
    'price' => 'decimal:2',
    'rating' => 'decimal:2',
    'stock' => 'integer',
    'popularity' => 'integer',
    'attributes' => 'array',
  ];
}
