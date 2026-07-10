<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('invoice_no', 255)->after('id')->index()->nullable();
            $table->date('date')->after('invoice_no')->nullable();
            $table->string('payable_id')->after('date')->index()->nullable();
            $table->string('payable_type')->after('payable_id')->nullable();
            $table->string('reference_id')->after('payable_type')->index()->nullable();
            $table->string('reference_type')->after('reference_id')->nullable();
            $table->string('remarks')->after('txn_code')->nullable();
            $table->unsignedBigInteger('user_id')->after('txn_code')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('invoice_no');
            $table->dropColumn('date');
            $table->dropColumn('payable_id');
            $table->dropColumn('payable_type');
            $table->dropColumn('remarks');
            $table->dropColumn('user_id');
        });
    }
}
