<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpireDateColumnInCouponCustomerAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupon_customer_assignments', function (Blueprint $table) {
            $table->timestamp('expire_date')->nullable()->after('assigned_by');
        });

        // Set default expire_date based on created_at + 30 days for existing records
        $assignments = \App\Models\CouponCustomerAssignment::all();
        foreach ($assignments as $assignment) {
            $assignment->expire_date = $assignment->created_at->addDays(30);
            $assignment->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coupon_customer_assignments', function (Blueprint $table) {
            $table->dropColumn('expire_date');
        });
    }
}
