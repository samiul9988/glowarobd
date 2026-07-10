<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddZoneAndCityNameToPathaoAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pathao_areas', function (Blueprint $table) {
            $table->string('city_name')->after('city_id')->nullable();
            $table->string('zone_name')->after('zone_id')->nullable();
            $table->text('full_area_name')->after('area_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pathao_areas', function (Blueprint $table) {
            $table->dropColumn('city_name');
            $table->dropColumn('zone_name');
            $table->dropColumn('full_area_name');
        });
    }
}
