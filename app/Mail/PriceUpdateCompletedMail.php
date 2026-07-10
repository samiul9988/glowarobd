<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PriceUpdateCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public int $merchantId;
    public array $data;

    public function __construct(int $merchantId, array $data)
    {
        $this->merchantId = $merchantId;
        $this->data = $data;
    }

    public function build()
    {
        $queryStrings = [];
        if(!empty($this->data['brand_id'] ?? null)){
            $queryStrings['brand_id'] = $this->data['brand_id'];
        }
        if(!empty($this->data['category_id'] ?? null)){
            $queryStrings['category_id'] = $this->data['category_id'];
        }
        if(!empty($this->data['search'] ?? null)){
            $queryStrings['search'] = $this->data['search'];
        }
        return $this->subject('Products Price Update Request Completed for Merchant ID ' . $this->merchantId)
            ->markdown('emails.price_update_completed', [
                'merchantId' => $this->merchantId,
                'link' => route('merchant_products.index', $queryStrings),
                'amount' => $this->data['amount'] ?? 0,
                'price_type' => $this->data['price_type'] ?? 'flat',
            ]);
    }
}
