<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoticesTable extends Migration
{
    public function up()
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notice_category_id');
            $table->string('title');
            $table->text('content');
            $table->enum('status', ['published', 'draft', 'scheduled'])->default('draft');
            $table->timestamp('publish_at')->nullable();
            $table->enum('visibility', ['customers', 'staffs', 'both'])->default('both');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notices');
    }
}