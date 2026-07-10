<?php

use App\Models\MerchantProduct;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomTimestampColumnsToMerchantProductsTable extends Migration
{
    public function up()
    {
        Schema::table('merchant_products', function (Blueprint $table) {
            $table->timestamp('pushed_at')->nullable()->after('last_price');
            $table->timestamp('price_updated_at')->nullable()->after('pushed_at');
        });

        // Optionally, you can set the current timestamp for existing records
        MerchantProduct::get()->each(function ($merchantProduct) {
            $merchantProduct->price_updated_at = $merchantProduct->updated_at;
            $merchantProduct->save();
        });
    }

    public function down()
    {
        Schema::table('merchant_products', function (Blueprint $table) {
            $table->dropColumn('pushed_at');
            $table->dropColumn('price_updated_at');
        });
    }
}
