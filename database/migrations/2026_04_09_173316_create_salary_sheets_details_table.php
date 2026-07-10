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
        Schema::create('salary_sheets_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_sheets_id')->constrained()->cascadeOnDelete();

            // Foreign keys
            $table->integer('staff_id');
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');

            // Attendance summary (snapshot)
            $table->json('attendance_summary'); // working_days, present_days, absent_days, leave_days, late_count
            $table->json('working_summary'); // work_minutes, overtime_minutes, late_minutes, early_leave_minutes
            $table->json('leave_summary'); // total_leave_days, paid_leave_days, unpaid_leave_days

            // Bonuses detail (array of bonus objects)
            $table->json('bonuses')->nullable();
            $table->decimal('bonus_total', 12, 2)->default(0);

            // Salary figures
            $table->decimal('profile_salary', 12, 2)->default(0);
            $table->decimal('working_hours_per_day', 5, 2)->default(0);
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('overtime_amount', 12, 2)->default(0);
            $table->decimal('late_fee_amount', 12, 2)->default(0);
            $table->decimal('leave_amount', 12, 2)->default(0)->comment('Deduction amount for unpaid leaves');
            $table->decimal('adjustment_amount', 12, 2)->default(0);
            $table->decimal('gross_salary', 12, 2)->default(0)->comment('Basic salary + overtime + bonuses');
            $table->decimal('net_salary', 12, 2)->default(0)->comment('Gross salary - late fees - leave amount +/- adjustment_amount');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_sheets_details');
    }
};
