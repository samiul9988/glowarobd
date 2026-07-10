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
        Schema::table('staff', function (Blueprint $table) {
            // Profile fields
            $table->after('role_id', function (Blueprint $table) {
                $table->string('employee_id', 100)->nullable()->unique();
                $table->integer('profile_picture')->nullable(); // upload ID
                $table->text('address')->nullable();
                $table->decimal('salary', 12, 2)->nullable();
                $table->text('educational_background')->nullable(); // comma-separated
                $table->date('joining_date')->nullable();
                $table->string('shift', 20)->nullable(); // Morning, Evening, Night
                $table->decimal('working_hours', 5, 2)->nullable();
                $table->json('weekly_offday')->nullable(); // array of day names
                $table->json('emergency_contact')->nullable();
                // {father_name, mother_name, spouse_name, contact_number}
            });

            // Hr fields
            $table->after('working_hours', function (Blueprint $table) {
                $table->string('employment_status', 30)->default('active');
                $table->string('blood_group', 5)->nullable();
                $table->date('resign_date')->nullable();
                $table->integer('resignation_letter')->nullable(); // upload ID
                $table->date('termination_date')->nullable();
                $table->text('termination_reason')->nullable();
                $table->json('bank_account')->nullable();
                $table->text('note')->nullable();
            });
        });

        foreach (\App\Models\Staff::all() as $staff) {
            // Set default employment status
            if (empty($staff->employee_id)) {
                $staff->employee_id = \App\Models\Staff::generateEmployeeId();
                $staff->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn([
                'employee_id',
                'profile_picture',
                'address',
                'salary',
                'educational_background',
                'joining_date',
                'shift',
                'working_hours',
                'weekly_offday',
                'emergency_contact',
                'employment_status',
                'blood_group',
                'resign_date',
                'resignation_letter',
                'termination_date',
                'termination_reason',
                'bank_account',
                'note',
            ]);
        });
    }
};
