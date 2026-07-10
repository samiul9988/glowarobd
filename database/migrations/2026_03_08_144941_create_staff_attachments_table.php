<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('staff_attachments', function (Blueprint $table) {
            $table->id();
            $table->integer('staff_id');
            $table->string('type', 50); // cv, nid, certificate
            $table->string('label', 255)->nullable();
            $table->integer('upload_id'); // references uploads table via aiz uploader
            $table->timestamps();

            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_attachments');
    }
};
