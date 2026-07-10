<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('applications');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Nullable morph
            $table->unsignedBigInteger('applicable_id')->nullable();
            $table->string('applicable_type')->nullable();

            $table->string('subject', 300);
            $table->text('content')->nullable();
            $table->string('type', 50); // e.g., leave, casual, etc.
            $table->json('attachments')->nullable();
            $table->string('status', 50)->default('pending');
            $table->text('note')->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('modified_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['applicable_id', 'applicable_type']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('applications');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
