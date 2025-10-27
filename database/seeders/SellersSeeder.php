<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SellersSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $sellers = [
      ['name' => 'TechWorld Store', 'rating' => 4.8],
      ['name' => 'Fashion Hub', 'rating' => 4.5],
      ['name' => 'Baby Paradise', 'rating' => 4.9],
      ['name' => 'Home Essentials', 'rating' => 4.3],
      ['name' => 'Sports Zone', 'rating' => 4.6],
      ['name' => 'Beauty Express', 'rating' => 4.7],
      ['name' => 'BookWorm\'s Corner', 'rating' => 4.4],
      ['name' => 'Garden Master', 'rating' => 4.2],
      ['name' => 'ElectroMart', 'rating' => 4.5],
      ['name' => 'StyleCraft', 'rating' => 4.1],
      ['name' => 'FitLife Store', 'rating' => 4.8],
      ['name' => 'Kitchen Pro', 'rating' => 4.6],
      ['name' => 'Mobile World', 'rating' => 4.7],
      ['name' => 'Outdoor Adventures', 'rating' => 4.3],
      ['name' => 'Luxury Watches', 'rating' => 4.9],
      ['name' => 'Game Central', 'rating' => 4.4],
      ['name' => 'Natural Health', 'rating' => 4.2],
      ['name' => 'Audio Paradise', 'rating' => 4.8],
      ['name' => 'Kids Kingdom', 'rating' => 4.5],
      ['name' => 'Furniture Plus', 'rating' => 4.1],
      ['name' => 'Smart Living', 'rating' => 4.6],
      ['name' => 'Wellness Hub', 'rating' => 4.3],
      ['name' => 'Vintage Collections', 'rating' => 4.0],
      ['name' => 'Urban Style', 'rating' => 4.4],
      ['name' => 'Pet Paradise', 'rating' => 4.7],
    ];

    foreach ($sellers as $seller) {
      DB::table('sellers')->insert([
        'id' => Str::uuid(),
        'name' => $seller['name'],
        'rating' => $seller['rating'],
        'created_at' => now(),
        'updated_at' => now(),
      ]);
    }
  }
}
