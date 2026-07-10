<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRewardPointLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reward_point_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('activity_id')->nullable();
            $table->string('activity_type')->nullable();
            $table->string('activity')->nullable();
            $table->bigInteger('earned')->nullable();
            $table->bigInteger('spent')->nullable();
            $table->text('activity_str')->nullable();
            $table->bigInteger('purpose_id')->nullable();
            $table->text('purpose_str')->nullable();
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
        Schema::dropIfExists('reward_point_logs');
    }
}
