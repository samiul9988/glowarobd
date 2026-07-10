<?php

namespace Modules\Waitlist\Emails;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProductBackInStockMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 60; // 1 minute between retries

    public $product;
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->product->name . ' is back in stock!')
            ->view('waitlist::emails.product_back_in_stock')
            ->with(['product' => $this->product]);
    }
}
