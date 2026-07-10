<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeColumnsIndexedInOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['delivery_status', 'created_at']);
            $table->index(['order_type', 'delivery_status']);
            $table->index(['order_source']);

            $table->string('code', 255)->change();
            $table->index(['code']);
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
            $table->dropIndex(['delivery_status', 'created_at']);
            $table->dropIndex(['order_type', 'delivery_status']);
            $table->dropIndex(['order_source']);
            $table->dropIndex(['code']);
        });
    }
}