<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_transactions', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index()->nullable();
            $table->unsignedBigInteger('user_id')->index()->nullable();
            $table->string('vno')->nullable();
            $table->string('head')->nullable()->index()->nullable();
            $table->string('head_type')->nullable();
            $table->unsignedBigInteger('head_id')->index()->nullable();
            $table->decimal('debit', 12)->default(0.00)->index()->nullable();
            $table->decimal('credit', 12)->default(0.00)->index()->nullable();
            $table->string('note')->nullable();
            $table->string('image')->nullable();
            $table->string('description')->nullable();
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
        Schema::dropIfExists('acc_transactions');
    }
}
