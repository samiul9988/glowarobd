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
        Schema::create('mail_templates', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('type', 255)->nullable();
            $table->string('subject', 255)->nullable();
            $table->longText('content')->nullable();
            $table->longText('sample_content')->nullable();
            $table->json('code')->nullable();
            $table->tinyInteger('status')->default(0)->comment('Active:1, Deactive:0');
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
        Schema::dropIfExists('mail_templates');
    }
};
