<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeSellerIdColumnNullableInPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // Check if the column exists before modifying it
            if (Schema::hasColumn('payments', 'seller_id')) {
                $table->unsignedBigInteger('seller_id')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            // Check if the column exists before modifying it
            if (Schema::hasColumn('payments', 'seller_id')) {
                $table->unsignedBigInteger('seller_id')->nullable(false)->change();
            }
        });
    }
}
