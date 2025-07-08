<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Supplier;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Create categories
        $categories = ['Makanan', 'Minuman'];
        $categoryIds = [];
        foreach ($categories as $categoryName) {
            $category = Category::firstOrCreate(['name' => $categoryName]);
            $categoryIds[$categoryName] = $category->id;
        }

        // Create units
        $units = ['pcs', 'botol', 'kg', 'gram'];
        $unitIds = [];
        foreach ($units as $unitName) {
            $unit = Unit::firstOrCreate(['name' => $unitName]);
            $unitIds[$unitName] = $unit->id;
        }

        // Create suppliers
        $suppliers = ['Supplier A', 'Supplier B', 'Supplier C'];
        $supplierIds = [];
        foreach ($suppliers as $supplierName) {
            $supplier = Supplier::firstOrCreate(['name' => $supplierName],
            [
                'email' => $faker->email,
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
            ]);
            $supplierIds[$supplierName] = $supplier->id;
        }

        // Create products
        $products = [
            ['name' => 'Nasi Goreng', 'category' => 'Makanan'],
            ['name' => 'Mie Goreng', 'category' => 'Makanan'],
            ['name' => 'Ayam Bakar', 'category' => 'Makanan'],
            ['name' => 'Soto Ayam', 'category' => 'Makanan'],
            ['name' => 'Gado-Gado', 'category' => 'Makanan'],
            ['name' => 'Es Teh Manis', 'category' => 'Minuman'],
            ['name' => 'Es Jeruk', 'category' => 'Minuman'],
            ['name' => 'Air Mineral', 'category' => 'Minuman'],
            ['name' => 'Kopi Hitam', 'category' => 'Minuman'],
            ['name' => 'Teh Tawar', 'category' => 'Minuman'],
        ];

        foreach ($products as $productData) {
            $categoryName = $productData['category'];
            $unitName = $faker->randomElement($units);
            $supplierName = $faker->randomElement($suppliers);

            \App\Models\Product::create([
                'name' => $productData['name'],
                'description' => $faker->sentence,
                'price_buy' => $price_buy = $faker->numberBetween(10000, 50000),
                'price_sell' => $faker->numberBetween($price_buy + 5000, $price_buy + 20000),
                'stock' => $faker->randomNumber(2),
                'category_id' => $categoryIds[$categoryName],
                'unit_id' => $unitIds[$unitName],
                'supplier_id' => $supplierIds[$supplierName],
                // 'barcode' => $faker->ean13,
                'status' => 1,
                // 'image' => $faker->imageUrl(200, 200, 'food', true),
            ]);
        }
    }
}
