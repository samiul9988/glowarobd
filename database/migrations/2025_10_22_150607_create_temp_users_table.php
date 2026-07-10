<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempUsersTable extends Migration
{
    public function up()
    {
        Schema::create('temp_users', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('temp_user_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('temp_users');
    }
}
