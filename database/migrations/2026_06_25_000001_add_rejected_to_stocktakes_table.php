<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support ENUM — only run the ALTER on MySQL/MariaDB
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE stocktakes MODIFY status ENUM('draft','pending','approved','rejected') NOT NULL DEFAULT 'draft'");
        }

        Schema::table('stocktakes', function (Blueprint $table) {
            $table->text('rejected_reason')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('stocktakes', function (Blueprint $table) {
            $table->dropColumn('rejected_reason');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE stocktakes MODIFY status ENUM('draft','pending','approved') NOT NULL DEFAULT 'draft'");
        }
    }
};
