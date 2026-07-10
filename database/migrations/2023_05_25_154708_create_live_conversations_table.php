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
        Schema::create('live_conversations', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('live_messages_id')->nullable();
            $table->integer('msg_from')->nullable();
            $table->integer('msg_to')->nullable();
            $table->longText('content')->nullable();
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
        Schema::dropIfExists('live_conversations');
    }
};
