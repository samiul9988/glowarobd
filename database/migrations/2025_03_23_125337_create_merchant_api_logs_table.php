<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantApiLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_api_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Merchant ID (nullable for guests)
            $table->string('method'); // HTTP method
            $table->string('url'); // Request URL
            $table->json('payload')->nullable(); // Request payload
            $table->json('response')->nullable(); // API response
            $table->string('response_code')->nullable(); // API response
            $table->float('response_time')->nullable(); // Response time in milliseconds
            $table->string('ip'); // User IP address
            $table->string('user_agent')->nullable(); // User agent
            $table->timestamps(); // Created at and updated at

            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchant_api_logs');
    }
}
