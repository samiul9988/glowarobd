<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccVoucherEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_voucher_entries', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index()->nullable();
            $table->string('vno', 255)->index()->nullable();
            $table->string('voucher_type')->default('general')->nullable();
            $table->string('entry_type')->nullable();
            $table->decimal('debit', 12)->default(0.00)->nullable();
            $table->decimal('credit', 12)->default(0.00)->nullable();
            $table->string('particular_id')->index()->nullable();
            $table->string('particular_type')->index()->nullable();
            $table->string('naration')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acc_voucher_entries');
    }
}
