<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CretateCrmOrdersFeedbacksTable extends Migration
{
    public function up()
    {
        Schema::create('crm_orders_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->json('feedback')->nullable();
            $table->string('note')->nullable();
            $table->integer('rating')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('crm_orders_feedbacks');
    }
}
