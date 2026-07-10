<?php

namespace App\Providers;

use App\Events\OrderPlaced;
use App\Events\InsertAccHead;
use App\Events\UpdateAccHead;
use App\Listeners\AdjustStock;
use App\Events\ProductPurchased;
use App\Listeners\FixProductStock;
use App\Listeners\CreateNewAccHead;
use App\Events\InsertAccTransaction;
use App\Events\ProductStockAdjusted;
use App\Events\ProductStockAffected;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Listeners\UpdateAccHeadListener;
use App\Listeners\CreateStockTransaction;
use App\Listeners\UpdateStockWhenAdjusted;
use App\Listeners\UpdateStockWhenPurchased;
use App\Listeners\InsertAccTransactionListener;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
  /**
   * The event listener mappings for the application.
   *
   * @var array
   */
  protected $listen = [
    Registered::class => [
      SendEmailVerificationNotification::class,
    ],
    OrderPlaced::class => [
      AdjustStock::class,
    ],
    ProductStockAffected::class => [
      CreateStockTransaction::class,
      FixProductStock::class,
    ],
    ProductPurchased::class => [
      UpdateStockWhenPurchased::class,
    ],
    ProductStockAdjusted::class => [
      UpdateStockWhenAdjusted::class,
    ],
    InsertAccHead::class => [
      CreateNewAccHead::class,
    ],
    UpdateAccHead::class => [
      UpdateAccHeadListener::class,
    ],
    InsertAccTransaction::class => [
      InsertAccTransactionListener::class,
    ]
  ];

  /**
   * Register any events for your application.
   *
   * @return void
   */
  public function boot()
  {
    parent::boot();

    //
  }
}
