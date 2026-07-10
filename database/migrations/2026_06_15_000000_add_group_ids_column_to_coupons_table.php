<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGroupIdsColumnToCouponsTable extends Migration
{
    public function up()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropIndex(['group_id']);
            $table->dropColumn('group_id');
            $table->json('group_ids')->nullable()->after('assigned_by');
            $table->text('description')->nullable()->after('group_ids');
            $table->boolean('only_for_app')->default(false)->after('force_apply')->index();
            $table->boolean('featured')->default(false)->after('only_for_app')->index();
        });
    }

    public function down()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('group_ids');
            $table->dropColumn('only_for_app');
            $table->dropColumn('featured');
            $table->dropColumn('description');
            $table->integer('group_id')->nullable()->after('assigned_by');
            $table->foreign('group_id')
                ->references('id')
                ->on('customer_groups')
                ->nullOnDelete();
            $table->index('group_id');
        });
    }
}
