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
        Schema::table('stock_adjust_items', function (Blueprint $table) {
            $table->string('adjust_type')->nullable()->after('variant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_adjust_items', function (Blueprint $table) {
            $table->dropColumn('adjust_type');
        });
    }
};
