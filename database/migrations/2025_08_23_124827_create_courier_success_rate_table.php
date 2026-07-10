<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourierSuccessRateTable extends Migration
{
    public function up()
    {
        Schema::create('courier_success_rate', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique();
            $table->json('summary')->nullable();
            $table->string('success_rate')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('phone');
        });
    }

    public function down()
    {
        Schema::dropIfExists('courier_success_rate');
    }
}
