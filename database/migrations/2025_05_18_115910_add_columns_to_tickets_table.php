<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('code', 255)->nullable()->change()->index();
            $table->string('ticket_type', 255)->default(\App\Models\Ticket::class)->after('code');
            $table->string('name')->nullable()->after('user_id');
            $table->string('phone')->nullable()->after('name');
            $table->string('issue')->nullable()->after('phone')->index();
            $table->string('priority')->default('low')->after('files')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['issue']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['code']);
            $table->dropColumn('ticket_type');
            $table->dropColumn('name');
            $table->dropColumn('phone');
            $table->dropColumn('issue');
            $table->dropColumn('priority');
            $table->string('code', 255)->change();
        });
    }
}
