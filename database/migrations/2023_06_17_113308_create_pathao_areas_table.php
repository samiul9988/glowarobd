<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePathaoAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pathao_areas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('city_id');
            $table->bigInteger('zone_id');
            $table->bigInteger('area_id');
            $table->string('area_name');
            $table->string('home_delivery_available')->nullable();
            $table->string('pickup_available')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pathao_areas');
    }
}
