<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notices', function (Blueprint $table) {
            $table->longText('content')->nullable()->change();
            $table->string('slug')->nullable()->after('title');
        });

        // Generate slugs for existing records
        DB::table('notices')->get()->each(function ($notice) {
            $slug = Str::slug($notice->title);
            DB::table('notices')->where('id', $notice->id)->update(['slug' => $slug. '-' . $notice->id]);
        });

        // Now make slug not nullable and add unique constraint
        Schema::table('notices', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notices', function (Blueprint $table) {
            // Reverse the content column change
            $table->text('content')->nullable()->change();
            
            // Remove the slug column and its index
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
}