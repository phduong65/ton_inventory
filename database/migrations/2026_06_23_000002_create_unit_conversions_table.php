<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('units')->onDelete('restrict');
            // factor: số đơn vị cơ sở tương ứng 1 đơn vị này
            // VD: 1 Thùng = 24 Lon → unit_id=Thùng, factor=24
            $table->decimal('factor', 12, 4);
            $table->string('note', 200)->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'unit_id']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_conversions');
    }
};
