<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderCallLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_call_logs', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('order_id')->index();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('called_by')->index();
            $table->decimal('duration', 8, 2)->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_call_logs');
    }
}
