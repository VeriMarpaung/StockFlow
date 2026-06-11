<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics',      'description' => 'Electronic devices and accessories'],
            ['name' => 'Clothing',         'description' => 'Apparel and fashion items'],
            ['name' => 'Food & Beverage',  'description' => 'Food products and drinks'],
            ['name' => 'Office Supplies',  'description' => 'Office and stationery items'],
            ['name' => 'Tools & Equipment','description' => 'Hardware tools and equipment'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
