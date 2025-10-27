<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoriesSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $categories = [
      // Baby & Mom
      ['name' => 'Baby & Mom', 'path' => 'Baby & Mom'],
      ['name' => 'Diapers', 'path' => 'Baby & Mom > Diapers'],
      ['name' => 'Baby Food', 'path' => 'Baby & Mom > Baby Food'],
      ['name' => 'Baby Care', 'path' => 'Baby & Mom > Baby Care'],
      ['name' => 'Toys', 'path' => 'Baby & Mom > Toys'],
      ['name' => 'Strollers', 'path' => 'Baby & Mom > Strollers'],

      // Electronics
      ['name' => 'Electronics', 'path' => 'Electronics'],
      ['name' => 'Smartphones', 'path' => 'Electronics > Smartphones'],
      ['name' => 'Laptops', 'path' => 'Electronics > Laptops'],
      ['name' => 'Tablets', 'path' => 'Electronics > Tablets'],
      ['name' => 'Audio', 'path' => 'Electronics > Audio'],
      ['name' => 'Gaming', 'path' => 'Electronics > Gaming'],
      ['name' => 'Smart Home', 'path' => 'Electronics > Smart Home'],

      // Fashion
      ['name' => 'Fashion', 'path' => 'Fashion'],
      ['name' => 'Women\'s Clothing', 'path' => 'Fashion > Women\'s Clothing'],
      ['name' => 'Men\'s Clothing', 'path' => 'Fashion > Men\'s Clothing'],
      ['name' => 'Shoes', 'path' => 'Fashion > Shoes'],
      ['name' => 'Accessories', 'path' => 'Fashion > Accessories'],
      ['name' => 'Watches', 'path' => 'Fashion > Watches'],

      // Home & Garden
      ['name' => 'Home & Garden', 'path' => 'Home & Garden'],
      ['name' => 'Furniture', 'path' => 'Home & Garden > Furniture'],
      ['name' => 'Kitchen', 'path' => 'Home & Garden > Kitchen'],
      ['name' => 'Bathroom', 'path' => 'Home & Garden > Bathroom'],
      ['name' => 'Garden Tools', 'path' => 'Home & Garden > Garden Tools'],
      ['name' => 'Decoration', 'path' => 'Home & Garden > Decoration'],

      // Sports
      ['name' => 'Sports', 'path' => 'Sports'],
      ['name' => 'Fitness', 'path' => 'Sports > Fitness'],
      ['name' => 'Outdoor', 'path' => 'Sports > Outdoor'],
      ['name' => 'Team Sports', 'path' => 'Sports > Team Sports'],
      ['name' => 'Water Sports', 'path' => 'Sports > Water Sports'],

      // Books & Media
      ['name' => 'Books & Media', 'path' => 'Books & Media'],
      ['name' => 'Books', 'path' => 'Books & Media > Books'],
      ['name' => 'Movies', 'path' => 'Books & Media > Movies'],
      ['name' => 'Music', 'path' => 'Books & Media > Music'],

      // Health & Beauty
      ['name' => 'Health & Beauty', 'path' => 'Health & Beauty'],
      ['name' => 'Skincare', 'path' => 'Health & Beauty > Skincare'],
      ['name' => 'Makeup', 'path' => 'Health & Beauty > Makeup'],
      ['name' => 'Hair Care', 'path' => 'Health & Beauty > Hair Care'],
      ['name' => 'Supplements', 'path' => 'Health & Beauty > Supplements'],
    ];

    foreach ($categories as $category) {
      DB::table('categories')->insert([
        'id' => Str::uuid(),
        'name' => $category['name'],
        'path' => $category['path'],
        'created_at' => now(),
        'updated_at' => now(),
      ]);
    }
  }
}
