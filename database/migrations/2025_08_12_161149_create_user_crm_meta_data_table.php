<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCrmMetaDataTable extends Migration
{
    public function up()
    {
        Schema::create('user_crm_meta_data', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();


            $table->string('key');
            $table->text('value')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['user_id', 'key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_crm_meta_data');
    }
}
