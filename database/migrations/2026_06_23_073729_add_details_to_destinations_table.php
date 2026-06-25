<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->string('code', 20)->nullable()->unique()->after('id');
            $table->string('phone', 20)->nullable()->after('name');
            $table->string('manager', 100)->nullable()->after('phone');
            $table->string('address', 255)->nullable()->after('manager');
            $table->text('note')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn(['code', 'phone', 'manager', 'address', 'note']);
        });
    }
};
