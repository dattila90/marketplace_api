<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Get category and seller IDs
    $categories = DB::table('categories')->pluck('id', 'name')->toArray();
    $sellers = DB::table('sellers')->pluck('id')->toArray();

    $products = [
      // Electronics - Smartphones
      [
        'title' => 'iPhone 15 Pro Max 256GB',
        'brand' => 'Apple',
        'category' => 'Smartphones',
        'price' => 1199.00,
        'currency' => 'USD',
        'stock' => 25,
        'rating' => 4.8,
        'popularity' => 95,
        'attributes' => json_encode([
          'storage' => '256GB',
          'color' => 'Natural Titanium',
          'screen_size' => '6.7"',
          'camera' => '48MP',
          'battery' => '4441mAh'
        ])
      ],
      [
        'title' => 'Samsung Galaxy S24 Ultra 512GB',
        'brand' => 'Samsung',
        'category' => 'Smartphones',
        'price' => 1299.00,
        'currency' => 'USD',
        'stock' => 18,
        'rating' => 4.7,
        'popularity' => 88,
        'attributes' => json_encode([
          'storage' => '512GB',
          'color' => 'Titanium Black',
          'screen_size' => '6.8"',
          'camera' => '200MP',
          'stylus' => true
        ])
      ],
      [
        'title' => 'Google Pixel 8 Pro 128GB',
        'brand' => 'Google',
        'category' => 'Smartphones',
        'price' => 999.00,
        'currency' => 'USD',
        'stock' => 32,
        'rating' => 4.6,
        'popularity' => 72,
        'attributes' => json_encode([
          'storage' => '128GB',
          'color' => 'Obsidian',
          'screen_size' => '6.7"',
          'ai_features' => true,
          'camera' => '50MP'
        ])
      ],

      // Electronics - Laptops
      [
        'title' => 'MacBook Pro 16" M3 Pro',
        'brand' => 'Apple',
        'category' => 'Laptops',
        'price' => 2499.00,
        'currency' => 'USD',
        'stock' => 12,
        'rating' => 4.9,
        'popularity' => 91,
        'attributes' => json_encode([
          'processor' => 'M3 Pro',
          'ram' => '18GB',
          'storage' => '512GB SSD',
          'screen_size' => '16"',
          'color' => 'Space Black'
        ])
      ],
      [
        'title' => 'Dell XPS 13 Plus',
        'brand' => 'Dell',
        'category' => 'Laptops',
        'price' => 1299.00,
        'currency' => 'USD',
        'stock' => 8,
        'rating' => 4.5,
        'popularity' => 68,
        'attributes' => json_encode([
          'processor' => 'Intel i7-13700H',
          'ram' => '16GB',
          'storage' => '512GB SSD',
          'screen_size' => '13.4"',
          'weight' => '2.73 lbs'
        ])
      ],
      [
        'title' => 'ASUS ROG Strix G15',
        'brand' => 'ASUS',
        'category' => 'Laptops',
        'price' => 1599.00,
        'currency' => 'USD',
        'stock' => 15,
        'rating' => 4.4,
        'popularity' => 76,
        'attributes' => json_encode([
          'processor' => 'AMD Ryzen 7',
          'gpu' => 'RTX 4060',
          'ram' => '16GB',
          'storage' => '1TB SSD',
          'gaming' => true
        ])
      ],

      // Electronics - Audio
      [
        'title' => 'Sony WH-1000XM5 Headphones',
        'brand' => 'Sony',
        'category' => 'Audio',
        'price' => 399.00,
        'currency' => 'USD',
        'stock' => 45,
        'rating' => 4.8,
        'popularity' => 89,
        'attributes' => json_encode([
          'noise_canceling' => true,
          'battery_life' => '30 hours',
          'wireless' => true,
          'color' => 'Black',
          'weight' => '250g'
        ])
      ],
      [
        'title' => 'Apple AirPods Pro 2nd Gen',
        'brand' => 'Apple',
        'category' => 'Audio',
        'price' => 249.00,
        'currency' => 'USD',
        'stock' => 67,
        'rating' => 4.7,
        'popularity' => 93,
        'attributes' => json_encode([
          'noise_canceling' => true,
          'battery_life' => '6 hours',
          'wireless_charging' => true,
          'spatial_audio' => true
        ])
      ],

      // Fashion - Women's Clothing
      [
        'title' => 'Elegant Evening Dress',
        'brand' => 'Zara',
        'category' => 'Women\'s Clothing',
        'price' => 89.90,
        'currency' => 'USD',
        'stock' => 24,
        'rating' => 4.3,
        'popularity' => 64,
        'attributes' => json_encode([
          'size' => 'M',
          'color' => 'Navy Blue',
          'material' => '95% Polyester, 5% Elastane',
          'occasion' => 'Formal',
          'length' => 'Midi'
        ])
      ],
      [
        'title' => 'Classic White Blouse',
        'brand' => 'H&M',
        'category' => 'Women\'s Clothing',
        'price' => 29.99,
        'currency' => 'USD',
        'stock' => 56,
        'rating' => 4.1,
        'popularity' => 78,
        'attributes' => json_encode([
          'size' => 'S',
          'color' => 'White',
          'material' => '100% Cotton',
          'style' => 'Button-down',
          'care' => 'Machine washable'
        ])
      ],

      // Fashion - Men's Clothing
      [
        'title' => 'Premium Leather Jacket',
        'brand' => 'Hugo Boss',
        'category' => 'Men\'s Clothing',
        'price' => 599.00,
        'currency' => 'USD',
        'stock' => 8,
        'rating' => 4.6,
        'popularity' => 57,
        'attributes' => json_encode([
          'size' => 'L',
          'color' => 'Black',
          'material' => 'Genuine Leather',
          'lining' => 'Polyester',
          'style' => 'Biker'
        ])
      ],
      [
        'title' => 'Cotton Polo Shirt',
        'brand' => 'Ralph Lauren',
        'category' => 'Men\'s Clothing',
        'price' => 89.00,
        'currency' => 'USD',
        'stock' => 43,
        'rating' => 4.4,
        'popularity' => 71,
        'attributes' => json_encode([
          'size' => 'M',
          'color' => 'Navy',
          'material' => '100% Cotton',
          'fit' => 'Classic',
          'logo' => 'Embroidered'
        ])
      ],

      // Fashion - Shoes
      [
        'title' => 'Nike Air Max 270',
        'brand' => 'Nike',
        'category' => 'Shoes',
        'price' => 150.00,
        'currency' => 'USD',
        'stock' => 34,
        'rating' => 4.5,
        'popularity' => 85,
        'attributes' => json_encode([
          'size' => '10',
          'color' => 'White/Black',
          'type' => 'Running',
          'material' => 'Mesh/Synthetic',
          'cushioning' => 'Air Max'
        ])
      ],
      [
        'title' => 'Adidas Ultraboost 23',
        'brand' => 'Adidas',
        'category' => 'Shoes',
        'price' => 190.00,
        'currency' => 'USD',
        'stock' => 28,
        'rating' => 4.6,
        'popularity' => 79,
        'attributes' => json_encode([
          'size' => '9.5',
          'color' => 'Core Black',
          'type' => 'Running',
          'boost_technology' => true,
          'primeknit_upper' => true
        ])
      ],

      // Baby & Mom - Diapers
      [
        'title' => 'Pampers Baby Dry Size 3 (92 count)',
        'brand' => 'Pampers',
        'category' => 'Diapers',
        'price' => 24.99,
        'currency' => 'USD',
        'stock' => 156,
        'rating' => 4.7,
        'popularity' => 94,
        'attributes' => json_encode([
          'size' => '3',
          'count' => 92,
          'weight_range' => '16-28 lbs',
          'overnight_protection' => true,
          'hypoallergenic' => true
        ])
      ],
      [
        'title' => 'Huggies Little Snugglers Size 2',
        'brand' => 'Huggies',
        'category' => 'Diapers',
        'price' => 27.99,
        'currency' => 'USD',
        'stock' => 134,
        'rating' => 4.6,
        'popularity' => 87,
        'attributes' => json_encode([
          'size' => '2',
          'count' => 84,
          'weight_range' => '12-18 lbs',
          'wetness_indicator' => true,
          'gentle_grip_tabs' => true
        ])
      ],

      // Baby & Mom - Baby Food
      [
        'title' => 'Gerber Organic Baby Food Variety Pack',
        'brand' => 'Gerber',
        'category' => 'Baby Food',
        'price' => 18.99,
        'currency' => 'USD',
        'stock' => 89,
        'rating' => 4.5,
        'popularity' => 76,
        'attributes' => json_encode([
          'age_range' => '6+ months',
          'organic' => true,
          'flavors' => ['Sweet Potato', 'Carrot', 'Peas'],
          'count' => 12,
          'bpa_free' => true
        ])
      ],

      // Home & Garden - Kitchen
      [
        'title' => 'KitchenAid Stand Mixer',
        'brand' => 'KitchenAid',
        'category' => 'Kitchen',
        'price' => 379.99,
        'currency' => 'USD',
        'stock' => 12,
        'rating' => 4.8,
        'popularity' => 82,
        'attributes' => json_encode([
          'capacity' => '5 quart',
          'color' => 'Empire Red',
          'attachments' => ['Dough Hook', 'Wire Whip', 'Flat Beater'],
          'power' => '325 watts',
          'warranty' => '1 year'
        ])
      ],
      [
        'title' => 'Instant Pot Duo 7-in-1',
        'brand' => 'Instant Pot',
        'category' => 'Kitchen',
        'price' => 99.95,
        'currency' => 'USD',
        'stock' => 45,
        'rating' => 4.7,
        'popularity' => 91,
        'attributes' => json_encode([
          'capacity' => '6 quart',
          'functions' => 7,
          'pressure_cooking' => true,
          'slow_cooking' => true,
          'programs' => 13
        ])
      ],

      // Home & Garden - Furniture
      [
        'title' => 'Modern Sectional Sofa',
        'brand' => 'IKEA',
        'category' => 'Furniture',
        'price' => 899.00,
        'currency' => 'USD',
        'stock' => 6,
        'rating' => 4.2,
        'popularity' => 58,
        'attributes' => json_encode([
          'material' => 'Fabric',
          'color' => 'Light Grey',
          'seats' => 5,
          'dimensions' => '120" x 80" x 35"',
          'assembly_required' => true
        ])
      ],
      [
        'title' => 'Ergonomic Office Chair',
        'brand' => 'Herman Miller',
        'category' => 'Furniture',
        'price' => 1395.00,
        'currency' => 'USD',
        'stock' => 4,
        'rating' => 4.9,
        'popularity' => 73,
        'attributes' => json_encode([
          'material' => 'Mesh',
          'color' => 'Black',
          'adjustable_height' => true,
          'lumbar_support' => true,
          'warranty' => '12 years'
        ])
      ],

      // Sports - Fitness
      [
        'title' => 'Adjustable Dumbbell Set',
        'brand' => 'Bowflex',
        'category' => 'Fitness',
        'price' => 549.00,
        'currency' => 'USD',
        'stock' => 18,
        'rating' => 4.6,
        'popularity' => 67,
        'attributes' => json_encode([
          'weight_range' => '5-52.5 lbs',
          'adjustable' => true,
          'space_saving' => true,
          'material' => 'Steel/Rubber',
          'includes_stand' => false
        ])
      ],
      [
        'title' => 'Yoga Mat Premium',
        'brand' => 'Manduka',
        'category' => 'Fitness',
        'price' => 88.00,
        'currency' => 'USD',
        'stock' => 67,
        'rating' => 4.4,
        'popularity' => 74,
        'attributes' => json_encode([
          'thickness' => '6mm',
          'material' => 'Natural Rubber',
          'size' => '71" x 24"',
          'eco_friendly' => true,
          'non_slip' => true
        ])
      ],

      // Health & Beauty - Skincare
      [
        'title' => 'Hyaluronic Acid Serum',
        'brand' => 'The Ordinary',
        'category' => 'Skincare',
        'price' => 8.90,
        'currency' => 'USD',
        'stock' => 234,
        'rating' => 4.3,
        'popularity' => 86,
        'attributes' => json_encode([
          'volume' => '30ml',
          'ingredients' => ['Hyaluronic Acid', 'Vitamin B5'],
          'skin_type' => 'All types',
          'cruelty_free' => true,
          'vegan' => true
        ])
      ],
      [
        'title' => 'Vitamin C Brightening Cream',
        'brand' => 'Olay',
        'category' => 'Skincare',
        'price' => 24.99,
        'currency' => 'USD',
        'stock' => 156,
        'rating' => 4.2,
        'popularity' => 71,
        'attributes' => json_encode([
          'volume' => '50ml',
          'spf' => 15,
          'vitamin_c' => true,
          'anti_aging' => true,
          'suitable_for' => 'Day use'
        ])
      ],

      // Books & Media - Books
      [
        'title' => 'The Seven Husbands of Evelyn Hugo',
        'brand' => 'St. Martin\'s Press',
        'category' => 'Books',
        'price' => 16.99,
        'currency' => 'USD',
        'stock' => 78,
        'rating' => 4.6,
        'popularity' => 89,
        'attributes' => json_encode([
          'author' => 'Taylor Jenkins Reid',
          'genre' => 'Historical Fiction',
          'pages' => 400,
          'format' => 'Paperback',
          'language' => 'English'
        ])
      ],
      [
        'title' => 'Atomic Habits',
        'brand' => 'Avery',
        'category' => 'Books',
        'price' => 13.49,
        'currency' => 'USD',
        'stock' => 145,
        'rating' => 4.8,
        'popularity' => 95,
        'attributes' => json_encode([
          'author' => 'James Clear',
          'genre' => 'Self-Help',
          'pages' => 320,
          'format' => 'Paperback',
          'bestseller' => true
        ])
      ],

      // Add more products to reach 80-100 total
      [
        'title' => 'Wireless Gaming Mouse',
        'brand' => 'Logitech',
        'category' => 'Gaming',
        'price' => 99.99,
        'currency' => 'USD',
        'stock' => 67,
        'rating' => 4.5,
        'popularity' => 78,
        'attributes' => json_encode([
          'dpi' => '25600',
          'wireless' => true,
          'rgb_lighting' => true,
          'battery_life' => '60 hours',
          'programmable_buttons' => 11
        ])
      ],
      [
        'title' => 'Smart Thermostat',
        'brand' => 'Nest',
        'category' => 'Smart Home',
        'price' => 249.00,
        'currency' => 'USD',
        'stock' => 23,
        'rating' => 4.4,
        'popularity' => 69,
        'attributes' => json_encode([
          'wifi_enabled' => true,
          'voice_control' => true,
          'energy_saving' => true,
          'auto_schedule' => true,
          'compatible_with' => ['Google', 'Alexa']
        ])
      ],
      [
        'title' => 'Bluetooth Speaker Waterproof',
        'brand' => 'JBL',
        'category' => 'Audio',
        'price' => 129.95,
        'currency' => 'USD',
        'stock' => 89,
        'rating' => 4.6,
        'popularity' => 81,
        'attributes' => json_encode([
          'waterproof' => 'IPX7',
          'battery_life' => '20 hours',
          'bluetooth_version' => '5.1',
          'color' => 'Blue',
          'portable' => true
        ])
      ],
      [
        'title' => 'Organic Baby Onesies 5-Pack',
        'brand' => 'Carter\'s',
        'category' => 'Baby Care',
        'price' => 19.99,
        'currency' => 'USD',
        'stock' => 234,
        'rating' => 4.7,
        'popularity' => 88,
        'attributes' => json_encode([
          'size' => '6-9 months',
          'material' => '100% Organic Cotton',
          'colors' => ['White', 'Grey', 'Yellow', 'Green', 'Pink'],
          'machine_washable' => true,
          'hypoallergenic' => true
        ])
      ],
      [
        'title' => 'Wooden Building Blocks Set',
        'brand' => 'Melissa & Doug',
        'category' => 'Toys',
        'price' => 29.99,
        'currency' => 'USD',
        'stock' => 145,
        'rating' => 4.8,
        'popularity' => 76,
        'attributes' => json_encode([
          'pieces' => 100,
          'material' => 'Natural Wood',
          'age_range' => '3+ years',
          'educational' => true,
          'non_toxic' => true
        ])
      ],
      [
        'title' => 'Stainless Steel Water Bottle',
        'brand' => 'Hydro Flask',
        'category' => 'Outdoor',
        'price' => 44.95,
        'currency' => 'USD',
        'stock' => 178,
        'rating' => 4.7,
        'popularity' => 84,
        'attributes' => json_encode([
          'capacity' => '32 oz',
          'insulation' => 'Double Wall',
          'keeps_cold' => '24 hours',
          'keeps_hot' => '12 hours',
          'color' => 'Pacific'
        ])
      ],
      [
        'title' => 'Memory Foam Pillow',
        'brand' => 'Tempur-Pedic',
        'category' => 'Bathroom',
        'price' => 149.00,
        'currency' => 'USD',
        'stock' => 56,
        'rating' => 4.5,
        'popularity' => 72,
        'attributes' => json_encode([
          'material' => 'Memory Foam',
          'size' => 'Standard',
          'cooling' => true,
          'removable_cover' => true,
          'warranty' => '5 years'
        ])
      ],
      [
        'title' => 'Electric Toothbrush',
        'brand' => 'Oral-B',
        'category' => 'Health & Beauty',
        'price' => 89.94,
        'currency' => 'USD',
        'stock' => 89,
        'rating' => 4.4,
        'popularity' => 75,
        'attributes' => json_encode([
          'brush_heads_included' => 3,
          'battery_life' => '14 days',
          'pressure_sensor' => true,
          'modes' => 3,
          'bluetooth' => true
        ])
      ],
      [
        'title' => 'Garden Tool Set',
        'brand' => 'Fiskars',
        'category' => 'Garden Tools',
        'price' => 79.99,
        'currency' => 'USD',
        'stock' => 34,
        'rating' => 4.3,
        'popularity' => 61,
        'attributes' => json_encode([
          'pieces' => 7,
          'includes' => ['Shovel', 'Rake', 'Pruner', 'Trowel', 'Gloves'],
          'material' => 'Steel/Wood',
          'storage_bag' => true,
          'warranty' => 'Lifetime'
        ])
      ],
      [
        'title' => 'LED Desk Lamp',
        'brand' => 'Philips',
        'category' => 'Decoration',
        'price' => 59.99,
        'currency' => 'USD',
        'stock' => 123,
        'rating' => 4.2,
        'popularity' => 68,
        'attributes' => json_encode([
          'led' => true,
          'dimmable' => true,
          'color_temperature' => 'Adjustable',
          'usb_charging' => true,
          'touch_control' => true
        ])
      ],
      [
        'title' => 'Running Shoes Men',
        'brand' => 'ASICS',
        'category' => 'Shoes',
        'price' => 120.00,
        'currency' => 'USD',
        'stock' => 67,
        'rating' => 4.6,
        'popularity' => 79,
        'attributes' => json_encode([
          'size' => '10.5',
          'color' => 'Black/White',
          'gel_cushioning' => true,
          'breathable_mesh' => true,
          'type' => 'Neutral'
        ])
      ],
      [
        'title' => 'Smartwatch Series 9',
        'brand' => 'Apple',
        'category' => 'Watches',
        'price' => 399.00,
        'currency' => 'USD',
        'stock' => 45,
        'rating' => 4.8,
        'popularity' => 92,
        'attributes' => json_encode([
          'size' => '45mm',
          'color' => 'Midnight',
          'gps' => true,
          'cellular' => false,
          'battery_life' => '18 hours',
          'water_resistant' => '50m'
        ])
      ],
      [
        'title' => 'Protein Powder Whey',
        'brand' => 'Optimum Nutrition',
        'category' => 'Supplements',
        'price' => 54.99,
        'currency' => 'USD',
        'stock' => 167,
        'rating' => 4.5,
        'popularity' => 83,
        'attributes' => json_encode([
          'flavor' => 'Double Rich Chocolate',
          'weight' => '5 lbs',
          'protein_per_serving' => '24g',
          'servings' => 74,
          'whey_isolate' => true
        ])
      ],
      [
        'title' => 'Hair Straightener Ceramic',
        'brand' => 'GHD',
        'category' => 'Hair Care',
        'price' => 199.00,
        'currency' => 'USD',
        'stock' => 23,
        'rating' => 4.7,
        'popularity' => 71,
        'attributes' => json_encode([
          'ceramic_plates' => true,
          'temperature' => '365°F',
          'heat_up_time' => '25 seconds',
          'auto_shutoff' => true,
          'cord_length' => '9 feet'
        ])
      ],
      [
        'title' => 'Makeup Palette Eyeshadow',
        'brand' => 'Urban Decay',
        'category' => 'Makeup',
        'price' => 54.00,
        'currency' => 'USD',
        'stock' => 89,
        'rating' => 4.6,
        'popularity' => 87,
        'attributes' => json_encode([
          'shades' => 12,
          'finish' => ['Matte', 'Shimmer', 'Metallic'],
          'cruelty_free' => true,
          'mirror_included' => true,
          'long_lasting' => true
        ])
      ],
      [
        'title' => 'Bluetooth Wireless Earbuds',
        'brand' => 'Bose',
        'category' => 'Audio',
        'price' => 279.00,
        'currency' => 'USD',
        'stock' => 56,
        'rating' => 4.5,
        'popularity' => 78,
        'attributes' => json_encode([
          'noise_canceling' => true,
          'battery_life' => '6 hours',
          'charging_case' => '18 additional hours',
          'water_resistant' => 'IPX4',
          'touch_controls' => true
        ])
      ],
      [
        'title' => 'Gaming Mechanical Keyboard',
        'brand' => 'Corsair',
        'category' => 'Gaming',
        'price' => 159.99,
        'currency' => 'USD',
        'stock' => 34,
        'rating' => 4.7,
        'popularity' => 81,
        'attributes' => json_encode([
          'switches' => 'Cherry MX Red',
          'backlight' => 'RGB',
          'wireless' => false,
          'programmable_keys' => true,
          'wrist_rest' => true
        ])
      ],
      [
        'title' => 'Cast Iron Skillet 12"',
        'brand' => 'Lodge',
        'category' => 'Kitchen',
        'price' => 34.90,
        'currency' => 'USD',
        'stock' => 178,
        'rating' => 4.8,
        'popularity' => 86,
        'attributes' => json_encode([
          'size' => '12 inch',
          'material' => 'Cast Iron',
          'pre_seasoned' => true,
          'oven_safe' => true,
          'dishwasher_safe' => false
        ])
      ],
      [
        'title' => 'Resistance Bands Set',
        'brand' => 'Fit Simplify',
        'category' => 'Fitness',
        'price' => 10.95,
        'currency' => 'USD',
        'stock' => 234,
        'rating' => 4.4,
        'popularity' => 89,
        'attributes' => json_encode([
          'bands' => 5,
          'resistance_levels' => ['X-Light', 'Light', 'Medium', 'Heavy', 'X-Heavy'],
          'door_anchor' => true,
          'carry_bag' => true,
          'instruction_guide' => true
        ])
      ],
      [
        'title' => 'Bamboo Cutting Board Set',
        'brand' => 'Totally Bamboo',
        'category' => 'Kitchen',
        'price' => 29.99,
        'currency' => 'USD',
        'stock' => 145,
        'rating' => 4.3,
        'popularity' => 74,
        'attributes' => json_encode([
          'pieces' => 3,
          'material' => '100% Bamboo',
          'sizes' => ['Small', 'Medium', 'Large'],
          'eco_friendly' => true,
          'antimicrobial' => true
        ])
      ],
      [
        'title' => 'Essential Oils Diffuser',
        'brand' => 'URPOWER',
        'category' => 'Health & Beauty',
        'price' => 25.99,
        'currency' => 'USD',
        'stock' => 198,
        'rating' => 4.2,
        'popularity' => 76,
        'attributes' => json_encode([
          'capacity' => '300ml',
          'runtime' => '10 hours',
          'led_lights' => 7,
          'auto_shutoff' => true,
          'ultrasonic' => true
        ])
      ],
      [
        'title' => 'Baby Stroller Lightweight',
        'brand' => 'Chicco',
        'category' => 'Strollers',
        'price' => 199.99,
        'currency' => 'USD',
        'stock' => 23,
        'rating' => 4.6,
        'popularity' => 72,
        'attributes' => json_encode([
          'weight' => '11 lbs',
          'one_hand_fold' => true,
          'cup_holder' => true,
          'sun_canopy' => true,
          'age_range' => '6-50 lbs'
        ])
      ],
      [
        'title' => 'Tablet 10.9-inch iPad Air',
        'brand' => 'Apple',
        'category' => 'Tablets',
        'price' => 599.00,
        'currency' => 'USD',
        'stock' => 34,
        'rating' => 4.8,
        'popularity' => 88,
        'attributes' => json_encode([
          'screen_size' => '10.9"',
          'storage' => '64GB',
          'color' => 'Sky Blue',
          'chip' => 'M1',
          'touch_id' => true
        ])
      ],
      [
        'title' => 'Coffee Maker Programmable',
        'brand' => 'Cuisinart',
        'category' => 'Kitchen',
        'price' => 79.95,
        'currency' => 'USD',
        'stock' => 67,
        'rating' => 4.4,
        'popularity' => 81,
        'attributes' => json_encode([
          'capacity' => '12 cups',
          'programmable' => true,
          'auto_shutoff' => true,
          'brew_strength' => 'Regular/Bold',
          'hot_plate' => true
        ])
      ],
      [
        'title' => 'Wireless Charging Pad',
        'brand' => 'Anker',
        'category' => 'Electronics',
        'price' => 25.99,
        'currency' => 'USD',
        'stock' => 234,
        'rating' => 4.3,
        'popularity' => 85,
        'attributes' => json_encode([
          'power' => '10W',
          'qi_certified' => true,
          'led_indicator' => true,
          'temperature_control' => true,
          'case_friendly' => true
        ])
      ],
      [
        'title' => 'Multivitamin Gummies Adult',
        'brand' => 'Vitafusion',
        'category' => 'Supplements',
        'price' => 12.47,
        'currency' => 'USD',
        'stock' => 289,
        'rating' => 4.5,
        'popularity' => 92,
        'attributes' => json_encode([
          'count' => 150,
          'flavors' => ['Berry', 'Peach', 'Orange'],
          'sugar' => '3g per serving',
          'gluten_free' => true,
          'no_artificial_flavors' => true
        ])
      ]
    ];

    foreach ($products as $product) {
      $categoryId = $categories[$product['category']] ?? null;
      $sellerId = $sellers[array_rand($sellers)];

      if ($categoryId) {
        DB::table('products')->insert([
          'id' => Str::uuid(),
          'title' => $product['title'],
          'brand' => $product['brand'],
          'category_id' => $categoryId,
          'price' => $product['price'],
          'currency' => $product['currency'],
          'stock' => $product['stock'],
          'seller_id' => $sellerId,
          'rating' => $product['rating'],
          'popularity' => $product['popularity'],
          'attributes' => $product['attributes'],
          'created_at' => now(),
          'updated_at' => now(),
        ]);
      }
    }
  }
}
