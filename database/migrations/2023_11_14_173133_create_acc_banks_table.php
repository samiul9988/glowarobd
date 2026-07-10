<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_banks', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('acc_name');
            $table->string('acc_no')->nullable();
            $table->string('type')->default('general_bank')->nullable(); // types are mobile_bank and general_bank
            $table->string('address')->nullable();
            $table->string('contact_no')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
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
        Schema::dropIfExists('acc_banks');
    }
}
