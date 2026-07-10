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
        Schema::create('return_suppliers', function (Blueprint $table) {
            $table->id();
            $table->integer('stock_adjust_id')->nullable()->unique();
            $table->unsignedInteger('user_id')->nullable();
            $table->bigInteger('supplier_id');
            $table->string('rs_number')->unique();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->date('date');
            $table->text('note')->nullable();

            $table->foreign('stock_adjust_id')->references('id')->on('stock_adjust')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('supplier_id')->references('id')->on('supplier')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_suppliers');
    }
};
