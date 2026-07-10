<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeftQtyAndExpireDateAndBarcodeColumnsToPurchaseOrderItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_order_item', function (Blueprint $table) {
            $table->integer('left_qty')->default(0)->after('qty');
            $table->date('expire_date')->nullable()->after('left_qty')->comment('Expiration date of the item');
            $table->string('barcode')->nullable()->after('expire_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_order_item', function (Blueprint $table) {
            $table->dropColumn('left_qty');
            $table->dropColumn('expire_date');
            $table->dropColumn('barcode');
        });
    }
}
