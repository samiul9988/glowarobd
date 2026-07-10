<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertizements', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('image', 255)->nullable();
            $table->string('ads_type', 31)->nullable();
            $table->string('link_type', 255)->nullable();
            $table->string('link', 255)->nullable();
            $table->longText('code')->nullable();
            $table->string('position', 255)->nullable();
            $table->tinyInteger('status')->default(0)->comment('Active:1, Deactive:0');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advertizements');
    }
};
