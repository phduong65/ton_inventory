<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->enum('type', ['IN', 'OUT', 'ADJUSTMENT']);
            $table->decimal('qty', 15, 3);
            $table->decimal('before_qty', 15, 3);
            $table->decimal('after_qty', 15, 3);
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->timestamp('created_at')->nullable();

            $table->index('product_id');
            $table->index('type');
            $table->index('created_at');
            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_ledger');
    }
};
