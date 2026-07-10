<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderDetail;
use App\Models\OrderReturn;
use App\Models\ProductStock;
use App\Models\OrderReturnItem;
use App\Jobs\ProcessPartialOrderReturnJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class PartialOrderReturnJobTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_can_process_partial_return_successfully()
    {
        // Create test data
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $productStock = ProductStock::factory()->create([
            'product_id' => $product->id,
            'qty' => 100
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'grand_total' => 200.00
        ]);

        $orderDetail = OrderDetail::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'price' => 100.00,
            'tax' => 10.00,
            'shipping_cost' => 15.00
        ]);

        $orderReturn = OrderReturn::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'reason' => 'Test return',
            'status' => 'approved',
            'is_partial' => true,
        ]);

        $orderReturnItem = OrderReturnItem::create([
            'order_return_id' => $orderReturn->id,
            'order_item_id' => $orderDetail->id,
            'quantity' => 2, // Returning 2 out of 5 items
        ]);

        // Get initial values
        $initialStock = $productStock->qty;
        $initialOrderTotal = $order->grand_total;
        $initialItemPrice = $orderDetail->price;
        $initialItemTax = $orderDetail->tax;
        $initialItemShipping = $orderDetail->shipping_cost;

        // Process the job
        $job = new ProcessPartialOrderReturnJob($orderReturn->id);
        $job->handle();

        // Refresh models
        $orderDetail->refresh();
        $order->refresh();
        $productStock->refresh();

        // Assert order detail is updated correctly
        $this->assertEquals(3, $orderDetail->quantity); // 5 - 2 = 3
        $this->assertEquals(60.00, $orderDetail->price); // $100 * (3/5) = $60
        $this->assertEquals(6.00, $orderDetail->tax); // $10 * (3/5) = $6
        $this->assertEquals(9.00, $orderDetail->shipping_cost); // $15 * (3/5) = $9

        // Assert stock is restored
        $this->assertEquals(102, $productStock->qty); // 100 + 2 = 102

        // Assert order total is recalculated
        // New total should be: price + tax + shipping = 60 + 6 + 9 = 75
        $this->assertEquals(75.00, $order->grand_total);

        // Assert order status is not changed (since it's partial)
        $this->assertNotEquals('returned', $order->delivery_status);
    }

    /** @test */
    public function it_marks_order_as_returned_when_all_items_are_returned()
    {
        // Create test data with single order detail
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $productStock = ProductStock::factory()->create([
            'product_id' => $product->id,
            'qty' => 100
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'grand_total' => 125.00,
            'delivery_status' => 'delivered'
        ]);

        $orderDetail = OrderDetail::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'price' => 100.00,
            'tax' => 10.00,
            'shipping_cost' => 15.00,
            'delivery_status' => 'delivered'
        ]);

        $orderReturn = OrderReturn::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'reason' => 'Test full return',
            'status' => 'approved',
            'is_partial' => true, // Even though we're returning all items
        ]);

        $orderReturnItem = OrderReturnItem::create([
            'order_return_id' => $orderReturn->id,
            'order_item_id' => $orderDetail->id,
            'quantity' => 5, // Returning all 5 items
        ]);

        // Process the job
        $job = new ProcessPartialOrderReturnJob($orderReturn->id);
        $job->handle();

        // Refresh models
        $orderDetail->refresh();
        $order->refresh();

        // Assert order detail is marked as returned
        $this->assertEquals(0, $orderDetail->quantity);
        $this->assertEquals('returned', $orderDetail->delivery_status);

        // Assert order is marked as returned
        $this->assertEquals('returned', $order->delivery_status);

        // Assert order total is 0
        $this->assertEquals(0.00, $order->grand_total);
    }

    /** @test */
    public function it_validates_return_quantity_does_not_exceed_order_quantity()
    {
        // This test would check that the job handles cases where return quantity
        // exceeds order quantity gracefully by skipping invalid items
        
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $productStock = ProductStock::factory()->create([
            'product_id' => $product->id,
            'qty' => 100
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'grand_total' => 125.00
        ]);

        $orderDetail = OrderDetail::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 100.00,
            'tax' => 10.00,
            'shipping_cost' => 15.00
        ]);

        $orderReturn = OrderReturn::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'reason' => 'Test invalid return',
            'status' => 'approved',
            'is_partial' => true,
        ]);

        $orderReturnItem = OrderReturnItem::create([
            'order_return_id' => $orderReturn->id,
            'order_item_id' => $orderDetail->id,
            'quantity' => 5, // Trying to return 5 when only 3 exist
        ]);

        $initialQuantity = $orderDetail->quantity;
        $initialPrice = $orderDetail->price;

        // Process the job
        $job = new ProcessPartialOrderReturnJob($orderReturn->id);
        $job->handle();

        // Refresh models
        $orderDetail->refresh();

        // Assert order detail is unchanged due to validation error
        $this->assertEquals($initialQuantity, $orderDetail->quantity);
        $this->assertEquals($initialPrice, $orderDetail->price);
    }
}
