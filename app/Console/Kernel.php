<?php
namespace App\Console;

use App\Console\Commands\CreateUniqueUserFromPosOrders;
use App\Console\Commands\ExpireRewardPoints;
use App\Console\Commands\FixProductVisitCount;
use App\Console\Commands\GenerateAttendance;
use App\Console\Commands\GenerateBlogCategorySitemap;
use App\Console\Commands\GenerateBlogSitemap;
use App\Console\Commands\GenerateBrandSitemap;
use App\Console\Commands\GenerateCategorySitemap;
use App\Console\Commands\GenerateExpireProductsList;
use App\Console\Commands\GenerateIndexSitemap;
use App\Console\Commands\GeneratePageSitemap;
use App\Console\Commands\GenerateProductSitemap;
use App\Console\Commands\GenerateStaticSitemap;
use App\Console\Commands\OrderFeedbacksCallLogId;
use App\Console\Commands\ProductPushCommand;
use App\Console\Commands\PublishScheduledNotices;
use App\Console\Commands\RemoveExpiredAssignedCouponsToCustomers;
use App\Console\Commands\SetCustomersGroup;
use App\Console\Commands\SyncStagingDatabase;
use App\Console\Commands\UnlockOrder;
use App\Jobs\CalculateClosingStock;
use App\Models\BusinessSetting;
use App\Models\MerchantApiLog;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Cache;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        GenerateBrandSitemap::class,
        GenerateCategorySitemap::class,
        GenerateProductSitemap::class,
        GenerateBlogSitemap::class,
        GenerateBlogCategorySitemap::class,
        GeneratePageSitemap::class,
        GenerateStaticSitemap::class,
        GenerateIndexSitemap::class,
        UnlockOrder::class,
        PublishScheduledNotices::class,
        ProductPushCommand::class,
        GenerateExpireProductsList::class,
        RemoveExpiredAssignedCouponsToCustomers::class,
        OrderFeedbacksCallLogId::class,
        SyncStagingDatabase::class,
        SetCustomersGroup::class,
        ExpireRewardPoints::class,
        FixProductVisitCount::class,
        CreateUniqueUserFromPosOrders::class,
        GenerateAttendance::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('pos:create-users')
        //     ->dailyAt('00:00')
        //     ->when(function () {
        //         return \App\Models\Order::whereNull('user_id')
        //             ->where('order_source', 'pos')
        //             ->whereNotNull('shipping_address->phone')
        //             ->exists();
        //     })
        //     ->runInBackground();

        $schedule->command('publish:scheduled')->everyMinute();

        $schedule->command('products:expire-soon')
            ->everyTwoHours()
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('insert:pathaoarea')
            ->dailyAt("02:00")
            ->runInBackground()
            ->withoutOverlapping();

        $schedule->command('coupons:remove-expire-assigned-coupons')
            ->dailyAt('00:00')
            ->runInBackground();

        $schedule->command('attendance:generate')
            ->dailyAt('00:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->when(fn() => get_setting('enable_attendance_management', 0) == 1);

        $schedule->command('attendance:checkout')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->when(fn() => get_setting('enable_attendance_management', 0) == 1);

        $schedule->command('customers:setgroup')
            ->days([Schedule::SATURDAY, Schedule::MONDAY, Schedule::WEDNESDAY])
            ->at('23:59')
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('reward:expire-points')
            ->dailyAt('23:59')
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('feed:facebook')
            ->dailyAt('00:00')
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('fix:call_log_id_in_feedbacks')
            ->dailyAt('23:59')
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('product:push rokomari')
            ->everyThirtyMinutes()
            ->runInBackground()
            ->withoutOverlapping()
            ->when(function () {
                return get_setting('enable_rokomari_service', 0) == 1 && get_setting('rokomari_products_pushed', 1) != 1;
            })
            ->onSuccess(function () {
                BusinessSetting::updateOrCreate(
                    ['type' => 'rokomari_products_pushed'],
                    ['value' => true]
                );
                Cache::forget('business_settings');
            });

        $schedule->command('model:prune', [
            '--model' => [MerchantApiLog::class],
        ])->dailyAt('23:59')
            ->timezone('Asia/Dhaka')
            ->runInBackground();

        $schedule->command('orders:unlock')
            ->everyMinute()
            ->runInBackground();

        $schedule->command('sitemap:index')
            ->twiceDaily(1, 13);

        // $schedule->command('db:sync-staging')
        //     ->weeklyOn(1, '00:00') // Every Monday at midnight
        //     ->withoutOverlapping()
        //     ->runInBackground();

        $schedule->call('App\Http\Controllers\PathaoController@generateOrMatchAreas')->everyTenMinutes();

        $schedule->job(new CalculateClosingStock)->monthlyOn(1, '00:00');

        $lastMonth = now()->subMonthNoOverflow();
        $schedule->job(
            new \App\Jobs\GenerateSalarySheet($lastMonth->year, $lastMonth->month)
        )->monthlyOn(1, '00:05');


        $schedule->command('queue:work --queue=high,default --tries=3 --timeout=60 --stop-when-empty')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
