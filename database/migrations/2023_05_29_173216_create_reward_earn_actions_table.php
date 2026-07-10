<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRewardEarnActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reward_earn_actions', function (Blueprint $table) {
            $table->id();
            $table->string('activity_type');
            $table->string('activity_title')->nullable();
            $table->bigInteger('earn_point')->default(0);
            $table->bigInteger('spent_amount')->nullable()->default(0);
            $table->string('purpose')->nullable();
            $table->bigInteger('validity')->nullable();
            $table->integer('status')->default(0);
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
        Schema::dropIfExists('reward_earn_actions');
    }
}
