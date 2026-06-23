<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocktakes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->enum('status', ['draft', 'pending', 'approved'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('stocktake_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stocktake_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->decimal('system_qty', 15, 3)->default(0);
            $table->decimal('actual_qty', 15, 3)->default(0);
            $table->decimal('variance', 15, 3)->default(0);
            $table->timestamps();

            $table->index('stocktake_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocktake_details');
        Schema::dropIfExists('stocktakes');
    }
};
