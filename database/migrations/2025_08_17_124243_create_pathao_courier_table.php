<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('pathao_courier', function (Blueprint $table) {
            $table->id();
            $table->uuid('secret_token')->unique();
            $table->text('token');
            $table->text('refresh_token');
            $table->string('expires_in');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pathao_courier');
    }
};
