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
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();

            $table->text('description');
            $table->string('role')->nullable();
            $table->text('benefits')->nullable();

            $table->string('location')->nullable();

            $table->string('employment_type')->default('full_time')->comment('full_time, part_time, internship');
            $table->unsignedDecimal('salary_min', 10, 2)->nullable();
            $table->unsignedDecimal('salary_max', 10, 2)->nullable();

            $table->string('experience')->nullable();

            $table->integer('vacancy')->default(1);
            $table->date('deadline')->nullable();

            $table->timestamp('published_at')->nullable();

            $table->string('status')->default('draft')->comment('draft, published, archived, scheduled')->index();

            $table->json('application_form')->nullable();

            // $table->unsignedInteger('created_by')->nullable();
            // $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
