<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeNameAndSlugColumnsUniqueInNoticeCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notice_categories', function (Blueprint $table) {
            $table->string('name')->unique()->change();
            $table->dropIndex('notice_categories_slug_index');
            $table->string('slug')->unique()->change();
        });
    }

    

    public function down()
    {
        Schema::table('notice_categories', function (Blueprint $table) {
            // Reverse the name column change
            $table->string('name')->unique(false)->change();
            
            // Drop the new unique constraint
            $table->dropUnique(['slug']);
            
            // Recreate the previous index if needed
            // $table->index('slug', 'notice_categories_slug_index');
        });
    }
}
