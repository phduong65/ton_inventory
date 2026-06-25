<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocktakes', function (Blueprint $table) {
            $table->foreignId('destination_id')
                  ->nullable()
                  ->after('category_id')
                  ->constrained('destinations')
                  ->onDelete('set null');

            $table->index('destination_id');
        });
    }

    public function down(): void
    {
        Schema::table('stocktakes', function (Blueprint $table) {
            $table->dropForeign(['destination_id']);
            $table->dropIndex(['destination_id']);
            $table->dropColumn('destination_id');
        });
    }
};
