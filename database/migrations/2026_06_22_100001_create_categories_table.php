<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('name', 100);
            $table->integer('sort')->default(0);
            $table->timestamps();

            $table->index('parent_id');
            $table->index('sort');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
