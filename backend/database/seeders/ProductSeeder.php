<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $electronics    = Category::where('name', 'Electronics')->first()->id;
        $clothing       = Category::where('name', 'Clothing')->first()->id;
        $foodBeverage   = Category::where('name', 'Food & Beverage')->first()->id;
        $officeSupplies = Category::where('name', 'Office Supplies')->first()->id;
        $tools          = Category::where('name', 'Tools & Equipment')->first()->id;

        $products = [
            // Electronics — mix of healthy and low stock
            ['category_id' => $electronics, 'name' => 'USB-C Hub 7-Port',       'sku' => 'ELEC-001', 'price' => 450000,  'stock' => 85,  'threshold' => 20],
            ['category_id' => $electronics, 'name' => 'Wireless Mouse',          'sku' => 'ELEC-002', 'price' => 280000,  'stock' => 7,   'threshold' => 10],
            ['category_id' => $electronics, 'name' => 'Mechanical Keyboard',     'sku' => 'ELEC-003', 'price' => 1200000, 'stock' => 22,  'threshold' => 15],
            ['category_id' => $electronics, 'name' => '27-inch Monitor',         'sku' => 'ELEC-004', 'price' => 3500000, 'stock' => 3,   'threshold' => 5],

            // Clothing
            ['category_id' => $clothing, 'name' => 'Cotton T-Shirt (M)',         'sku' => 'CLTH-001', 'price' => 120000,  'stock' => 150, 'threshold' => 30],
            ['category_id' => $clothing, 'name' => 'Slim Fit Jeans (32)',        'sku' => 'CLTH-002', 'price' => 350000,  'stock' => 5,   'threshold' => 15],
            ['category_id' => $clothing, 'name' => 'Running Shoes (42)',         'sku' => 'CLTH-003', 'price' => 780000,  'stock' => 40,  'threshold' => 10],

            // Food & Beverage — low stock demo
            ['category_id' => $foodBeverage, 'name' => 'Instant Coffee 200g',   'sku' => 'FOOD-001', 'price' => 75000,   'stock' => 200, 'threshold' => 50],
            ['category_id' => $foodBeverage, 'name' => 'Mineral Water 1.5L',    'sku' => 'FOOD-002', 'price' => 8000,    'stock' => 2,   'threshold' => 30],
            ['category_id' => $foodBeverage, 'name' => 'Green Tea Bags 25pcs',  'sku' => 'FOOD-003', 'price' => 35000,   'stock' => 90,  'threshold' => 20],

            // Office Supplies
            ['category_id' => $officeSupplies, 'name' => 'A4 Paper (500 sheets)','sku' => 'OFFC-001', 'price' => 65000,   'stock' => 300, 'threshold' => 50],
            ['category_id' => $officeSupplies, 'name' => 'Ballpoint Pen 12pcs', 'sku' => 'OFFC-002', 'price' => 28000,   'stock' => 8,   'threshold' => 20],
            ['category_id' => $officeSupplies, 'name' => 'Stapler Heavy-Duty',  'sku' => 'OFFC-003', 'price' => 95000,   'stock' => 45,  'threshold' => 10],

            // Tools & Equipment
            ['category_id' => $tools, 'name' => 'Cordless Drill 18V',           'sku' => 'TOOL-001', 'price' => 1850000, 'stock' => 12,  'threshold' => 5],
            ['category_id' => $tools, 'name' => 'Screwdriver Set 10pcs',        'sku' => 'TOOL-002', 'price' => 180000,  'stock' => 4,   'threshold' => 8],
        ];

        foreach ($products as $product) {
            Product::create(array_merge($product, ['description' => null]));
        }
    }
}
