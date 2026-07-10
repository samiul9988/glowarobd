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
        Schema::create('job_post_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained('job_posts')->cascadeOnDelete();
            $table->integer('staff_id')->nullable()->index();
            $table->string('applicant_name')->nullable();
            $table->string('applicant_email')->nullable();
            $table->string('applicant_phone')->nullable()->index();
            $table->string('subject')->nullable();
            $table->boolean('shortlisted')->default(false)->index();
            $table->string('status')->default('pending')->index(); // pending, reviewed, confirmed, hired, rejected
            $table->json('submitted_values');
            $table->json('field_snapshot');
            $table->json('uploaded_attachments')->nullable();
            $table->json('notes')->nullable();
            $table->integer('matching_score')->default(0)->index();
            $table->json('logs')->nullable();
            $table->timestamps();

            $table->index(['job_post_id', 'applicant_phone']);
            $table->index(['job_post_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_post_applications');
    }
};
