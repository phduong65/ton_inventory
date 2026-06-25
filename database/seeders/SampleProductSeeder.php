<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\UnitConversion;
use Illuminate\Database\Seeder;

class SampleProductSeeder extends Seeder
{
    public function run(): void
    {
        // --- Nhà cung cấp mẫu ---
        $nccBia   = Supplier::firstOrCreate(['code' => 'NCC-001'], [
            'name' => 'Công ty Bia Sài Gòn', 'phone' => '028-38123456',
            'email' => 'info@bsg.vn', 'address' => '187 Nguyễn Chí Thanh, Q.5, TP.HCM',
            'tax_code' => '0300521186', 'contact_person' => 'Nguyễn Văn A',
        ]);
        $nccRuou  = Supplier::firstOrCreate(['code' => 'NCC-002'], [
            'name' => 'Công ty TNHH Spirits VN', 'phone' => '024-39876543',
            'email' => 'sales@spiritsvn.com', 'address' => '45 Đinh Tiên Hoàng, Hà Nội',
            'tax_code' => '0100109106', 'contact_person' => 'Trần Thị B',
        ]);
        $nccThuc  = Supplier::firstOrCreate(['code' => 'NCC-003'], [
            'name' => 'Công ty Thực Phẩm Miền Nam', 'phone' => '028-37654321',
            'email' => 'order@tpmn.vn', 'address' => '12 Lý Tự Trọng, Q.1, TP.HCM',
            'tax_code' => '0300512345', 'contact_person' => 'Lê Văn C',
        ]);

        // --- Units ---
        $uChai  = Unit::where('name', 'Chai')->first();
        $uLon   = Unit::where('name', 'Lon')->first();
        $uThung = Unit::where('name', 'Thùng')->first();
        $uLoc   = Unit::where('name', 'Lốc')->first();
        $uCai   = Unit::where('name', 'Cái')->first();
        $uHop   = Unit::where('name', 'Hộp')->first();
        $uKg    = Unit::where('name', 'Kg')->first();
        $uLit   = Unit::where('name', 'Lít')->first();
        $uGoi   = Unit::where('name', 'Gói')->first();

        // --- Categories ---
        $whisky  = Category::where('name', 'Whisky')->first();
        $vodka   = Category::where('name', 'Vodka')->first();
        $gin     = Category::where('name', 'Gin')->first();
        $bia     = Category::where('name', 'Bia')->first();
        $soft    = Category::where('name', 'Soft Drink')->first();
        $tuoi    = Category::where('name', 'Thực phẩm tươi')->first();
        $kho     = Category::where('name', 'Thực phẩm khô')->first();
        $giavi   = Category::where('name', 'Gia vị')->first();

        // --- Sản phẩm mẫu ---
        $products = [
            // BAR — Bia (base: Lon)
            [
                'sku' => 'BIA-SG-330', 'name' => 'Bia Sài Gòn 330ml',
                'category_id' => $bia?->id, 'unit_id' => $uLon?->id,
                'default_price' => 10000, 'barcode' => '8934563143718',
                'conversions' => [
                    ['unit' => 'Lốc', 'factor' => 6],
                    ['unit' => 'Thùng', 'factor' => 24],
                ],
            ],
            [
                'sku' => 'BIA-TIG-330', 'name' => 'Bia Tiger 330ml',
                'category_id' => $bia?->id, 'unit_id' => $uLon?->id,
                'default_price' => 12000, 'barcode' => '8935049100023',
                'conversions' => [
                    ['unit' => 'Lốc', 'factor' => 6],
                    ['unit' => 'Thùng', 'factor' => 24],
                ],
            ],
            [
                'sku' => 'BIA-HN-330', 'name' => 'Bia Hà Nội 330ml',
                'category_id' => $bia?->id, 'unit_id' => $uLon?->id,
                'default_price' => 9000, 'barcode' => '8936007620105',
                'conversions' => [
                    ['unit' => 'Lốc', 'factor' => 6],
                    ['unit' => 'Thùng', 'factor' => 24],
                ],
            ],

            // BAR — Whisky (base: Chai)
            [
                'sku' => 'WHI-JWR-750', 'name' => 'Johnnie Walker Red Label 750ml',
                'category_id' => $whisky?->id, 'unit_id' => $uChai?->id,
                'default_price' => 420000, 'barcode' => '5000267014609',
                'conversions' => [
                    ['unit' => 'Thùng', 'factor' => 12],
                ],
            ],
            [
                'sku' => 'WHI-JWB-750', 'name' => 'Johnnie Walker Black Label 750ml',
                'category_id' => $whisky?->id, 'unit_id' => $uChai?->id,
                'default_price' => 750000, 'barcode' => '5000267024943',
                'conversions' => [
                    ['unit' => 'Thùng', 'factor' => 12],
                ],
            ],
            [
                'sku' => 'WHI-GLE-700', 'name' => 'Glenlivet 12 Year 700ml',
                'category_id' => $whisky?->id, 'unit_id' => $uChai?->id,
                'default_price' => 980000, 'barcode' => '5012629022199',
                'conversions' => [
                    ['unit' => 'Thùng', 'factor' => 6],
                ],
            ],

            // BAR — Vodka (base: Chai)
            [
                'sku' => 'VOD-ABL-700', 'name' => 'Absolut Vodka 700ml',
                'category_id' => $vodka?->id, 'unit_id' => $uChai?->id,
                'default_price' => 450000, 'barcode' => '7312040017560',
                'conversions' => [
                    ['unit' => 'Thùng', 'factor' => 12],
                ],
            ],

            // BAR — Gin (base: Chai)
            [
                'sku' => 'GIN-HEN-700', 'name' => "Hendrick's Gin 700ml",
                'category_id' => $gin?->id, 'unit_id' => $uChai?->id,
                'default_price' => 890000, 'barcode' => '5010462101604',
                'conversions' => [
                    ['unit' => 'Thùng', 'factor' => 6],
                ],
            ],

            // BAR — Soft Drink (base: Lon)
            [
                'sku' => 'COLA-330', 'name' => 'Coca-Cola 330ml',
                'category_id' => $soft?->id, 'unit_id' => $uLon?->id,
                'default_price' => 8000, 'barcode' => '5449000024428',
                'conversions' => [
                    ['unit' => 'Lốc', 'factor' => 6],
                    ['unit' => 'Thùng', 'factor' => 24],
                ],
            ],
            [
                'sku' => 'PEPSI-330', 'name' => 'Pepsi 330ml',
                'category_id' => $soft?->id, 'unit_id' => $uLon?->id,
                'default_price' => 7500, 'barcode' => '4901681310043',
                'conversions' => [
                    ['unit' => 'Lốc', 'factor' => 6],
                    ['unit' => 'Thùng', 'factor' => 24],
                ],
            ],

            // BẾP — Thực phẩm tươi (base: Kg)
            [
                'sku' => 'BEEF-001', 'name' => 'Thịt bò thăn nội',
                'category_id' => $tuoi?->id, 'unit_id' => $uKg?->id,
                'default_price' => 280000, 'barcode' => null,
                'conversions' => [],
            ],
            [
                'sku' => 'PORK-001', 'name' => 'Thịt heo ba chỉ',
                'category_id' => $tuoi?->id, 'unit_id' => $uKg?->id,
                'default_price' => 140000, 'barcode' => null,
                'conversions' => [],
            ],

            // BẾP — Thực phẩm khô (base: Gói)
            [
                'sku' => 'RICE-001', 'name' => 'Gạo ST25 5kg',
                'category_id' => $kho?->id, 'unit_id' => $uGoi?->id,
                'default_price' => 95000, 'barcode' => null,
                'conversions' => [],
            ],

            // BẾP — Gia vị (base: Chai)
            [
                'sku' => 'SOY-001', 'name' => 'Nước tương Maggi 700ml',
                'category_id' => $giavi?->id, 'unit_id' => $uChai?->id,
                'default_price' => 28000, 'barcode' => '8934563149000',
                'conversions' => [
                    ['unit' => 'Thùng', 'factor' => 12],
                ],
            ],
        ];

        foreach ($products as $data) {
            $conversions = $data['conversions'];
            unset($data['conversions']);

            $product = Product::firstOrCreate(
                ['sku' => $data['sku']],
                array_merge($data, ['status' => 'active']),
            );

            // Tạo inventory record nếu chưa có
            Inventory::firstOrCreate(
                ['product_id' => $product->id],
                ['quantity' => 0, 'average_cost' => 0],
            );

            // Tạo unit conversions
            foreach ($conversions as $conv) {
                $unit = Unit::where('name', $conv['unit'])->first();
                if ($unit) {
                    UnitConversion::firstOrCreate(
                        ['product_id' => $product->id, 'unit_id' => $unit->id],
                        ['factor' => $conv['factor']],
                    );
                }
            }
        }
    }
}
