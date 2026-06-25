<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Destination;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Điểm nhận hàng
        $destinations = [
            ['code' => 'KHO43', 'name' => 'Kho 43', 'phone' => '0901 234 567', 'manager' => 'Nguyễn Văn A', 'address' => 'Tầng 1, Khu A, 123 Nguyễn Huệ, Q.1, TP.HCM', 'note' => null],
            ['code' => 'KHO44', 'name' => 'Kho 44', 'phone' => '0901 234 568', 'manager' => 'Trần Thị B',  'address' => 'Tầng 2, Khu B, 123 Nguyễn Huệ, Q.1, TP.HCM', 'note' => null],
            ['code' => 'BARA',  'name' => 'Bar A',   'phone' => '0902 345 678', 'manager' => 'Lê Văn C',   'address' => 'Tầng 3, Khu A, 123 Nguyễn Huệ, Q.1, TP.HCM', 'note' => 'Bar chính tầng 3'],
            ['code' => 'BARB',  'name' => 'Bar B',   'phone' => '0902 345 679', 'manager' => 'Phạm Thị D', 'address' => 'Tầng 4, Khu B, 123 Nguyễn Huệ, Q.1, TP.HCM', 'note' => 'Bar VIP tầng 4'],
            ['code' => 'NHAHANG', 'name' => 'Nhà hàng', 'phone' => '0903 456 789', 'manager' => 'Hoàng Văn E', 'address' => 'Tầng 5, 123 Nguyễn Huệ, Q.1, TP.HCM', 'note' => null],
        ];
        foreach ($destinations as $dest) {
            $existing = Destination::where('name', $dest['name'])->first();
            if ($existing) {
                $existing->update($dest);
            } else {
                Destination::create($dest);
            }
        }

        // Đơn vị tính
        $units = [
            ['code' => 'CHAI',  'name' => 'Chai'],
            ['code' => 'LON',   'name' => 'Lon'],
            ['code' => 'THUNG', 'name' => 'Thùng'],
            ['code' => 'LOC',   'name' => 'Lốc'],
            ['code' => 'CAI',   'name' => 'Cái'],
            ['code' => 'HOP',   'name' => 'Hộp'],
            ['code' => 'TUI',   'name' => 'Túi'],
            ['code' => 'KG',    'name' => 'Kg'],
            ['code' => 'G',     'name' => 'g'],
            ['code' => 'LIT',   'name' => 'Lít'],
            ['code' => 'ML',    'name' => 'ml'],
            ['code' => 'BO',    'name' => 'Bộ'],
            ['code' => 'CUON',  'name' => 'Cuộn'],
            ['code' => 'GOI',   'name' => 'Gói'],
        ];
        foreach ($units as $unit) {
            Unit::firstOrCreate(['code' => $unit['code']], ['name' => $unit['name']]);
        }

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
