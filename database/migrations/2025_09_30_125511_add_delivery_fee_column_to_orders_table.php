<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryFeeColumnToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'delivery_fee')) {
                $table->decimal('delivery_fee', 10, 2)
                    ->nullable()
                    ->comment('This will be the actual shipping fee charged by the shipping service provider')
                    ->change();
            } else {
                $table->decimal('delivery_fee', 10, 2)
                    ->nullable()
                    ->after('delivery_status')
                    ->comment('This will be the actual shipping fee charged by the shipping service provider');
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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivery_fee');
        });
    }
}
