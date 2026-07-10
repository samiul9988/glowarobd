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
        Schema::create('salary_sheets', function (Blueprint $table) {
            $table->id();

            // Period
            $table->unsignedTinyInteger('month');  // 1–12
            $table->unsignedSmallInteger('year');

            // Who finalized / generated this record
            $table->unsignedInteger('generated_by')->nullable();
            $table->foreign('generated_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamp('generated_at')->nullable(); // When the sheet was generated
            $table->timestamps();

            // One result per period
            $table->unique(['month', 'year']);

            // Common query indexes
            $table->index(['month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_sheets');
    }
};
