<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTemplateIdColumnToSupplierTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supplier', function (Blueprint $table) {
            $table->unsignedBigInteger('template_id')->nullable()->after('address');
            $table->foreign('template_id')->references('id')->on('templates')->onDelete('set null');
            $table->index('template_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supplier', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropIndex(['template_id']);
            $table->dropColumn('template_id');
        });
    }
}
