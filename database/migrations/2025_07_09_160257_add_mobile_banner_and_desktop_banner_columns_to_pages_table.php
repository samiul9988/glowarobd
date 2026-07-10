<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMobileBannerAndDesktopBannerColumnsToPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('mobile_banner')->nullable()->after('content');
            $table->string('desktop_banner')->nullable()->after('mobile_banner');
        });

        $pageId = \App\Models\Page::where('type', 'custom_page')->first()->id;
        $lastPageId = \App\Models\Page::where('type','custom_page')->latest()->first()->id;
        foreach(\App\Models\Page::where('type', 'custom_page')->get() as $page) {
            $id = $page->id;
            $page->id = $lastPageId + 1;
            $page->save();

            $pageTranslation = \App\Models\PageTranslation::where('page_id', $id)->first();
            if ($pageTranslation) {
                $pageTranslation->page_id = $page->id;
                $pageTranslation->save();
            }

            $lastPageId++;
        }
        $page = new \App\Models\Page();
        $page->id = $pageId;
        $page->title = 'GlowaroTube';
        $page->slug = 'glowaro-tube';
        $page->type = 'video_tutorial_page';
        $page->content = '';
        $page->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['mobile_banner', 'desktop_banner']);
        });
    }
}
