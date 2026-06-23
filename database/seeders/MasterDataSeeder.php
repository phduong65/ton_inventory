<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Destination;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Điểm nhận hàng
        Destination::firstOrCreate(['name' => 'Kho 43']);
        Destination::firstOrCreate(['name' => 'Kho 44']);

        // Ngành hàng
        $bar = Category::firstOrCreate(['name' => 'BAR', 'parent_id' => null, 'sort' => 1]);
        $ruou = Category::firstOrCreate(['name' => 'Rượu', 'parent_id' => $bar->id, 'sort' => 1]);
        Category::firstOrCreate(['name' => 'Whisky', 'parent_id' => $ruou->id, 'sort' => 1]);
        Category::firstOrCreate(['name' => 'Vodka', 'parent_id' => $ruou->id, 'sort' => 2]);
        Category::firstOrCreate(['name' => 'Gin', 'parent_id' => $ruou->id, 'sort' => 3]);
        Category::firstOrCreate(['name' => 'Bia', 'parent_id' => $bar->id, 'sort' => 2]);
        Category::firstOrCreate(['name' => 'Soft Drink', 'parent_id' => $bar->id, 'sort' => 3]);

        $bep = Category::firstOrCreate(['name' => 'BẾP', 'parent_id' => null, 'sort' => 2]);
        Category::firstOrCreate(['name' => 'Thực phẩm tươi', 'parent_id' => $bep->id, 'sort' => 1]);
        Category::firstOrCreate(['name' => 'Thực phẩm khô', 'parent_id' => $bep->id, 'sort' => 2]);
        Category::firstOrCreate(['name' => 'Gia vị', 'parent_id' => $bep->id, 'sort' => 3]);

        $chung = Category::firstOrCreate(['name' => 'CHUNG', 'parent_id' => null, 'sort' => 3]);
        Category::firstOrCreate(['name' => 'Văn phòng phẩm', 'parent_id' => $chung->id, 'sort' => 1]);
        Category::firstOrCreate(['name' => 'Công cụ', 'parent_id' => $chung->id, 'sort' => 2]);
        Category::firstOrCreate(['name' => 'Khác', 'parent_id' => $chung->id, 'sort' => 3]);
    }
}
