<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConvertTablesToUtf8mb4 extends Migration
{
    public function up()
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Get all tables
        $tables = DB::select('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = "BASE TABLE"', [DB::getDatabaseName()]);

        // dd($tables);
        foreach ($tables as $table) {
            $tableName = $table->TABLE_NAME;
            DB::statement("ALTER TABLE `$tableName` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }

        // Convert database
        DB::statement("ALTER DATABASE `" . DB::getDatabaseName() . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        // Optional: Revert if needed (not recommended)
    }
}
