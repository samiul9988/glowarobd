<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyDateOfBirthColumnToUsersTable extends Migration
{
    public function up()
    {
        // First drop the column if it exists
        if (Schema::hasColumn('users', 'date_of_birth')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('date_of_birth');
            });
        }

        // Then add it as TIMESTAMP
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('date_of_birth')->nullable()->after('gender');
        });
    }

    public function down()
    {
        // First drop the column if it exists
        if (Schema::hasColumn('users', 'date_of_birth')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('date_of_birth');
            });
        }

        // Then add it as BIGINT
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('date_of_birth')->nullable()->after('gender');
        });
    }
}
