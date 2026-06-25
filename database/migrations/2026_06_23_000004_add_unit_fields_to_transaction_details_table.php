<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('product_id')->constrained('units')->onDelete('restrict');
            // Snapshot factor tại thời điểm tạo phiếu (tránh thay đổi conversion sau này ảnh hưởng phiếu cũ)
            $table->decimal('conversion_factor', 12, 4)->default(1)->after('unit_id');
            // base_qty = qty * conversion_factor — qty theo đơn vị cơ sở, dùng để cập nhật inventory
            $table->decimal('base_qty', 15, 3)->default(0)->after('conversion_factor');

            $table->index('unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'conversion_factor', 'base_qty']);
        });
    }
};
