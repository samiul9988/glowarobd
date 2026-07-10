<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBannerColumnToProductCustomFieldsTable extends Migration
{
    public function up()
    {
        Schema::table('product_custom_fields', function (Blueprint $table) {
            $table->bigInteger('banner')->nullable()->after('type');
        });
    }

    public function down()
    {
        Schema::table('product_custom_fields', function (Blueprint $table) {
            $table->dropColumn('banner');
        });
    }
}
