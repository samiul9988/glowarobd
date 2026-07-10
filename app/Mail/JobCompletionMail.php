<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class JobCompletionMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $mailType;
    public int $merchantId;
    public array $data;

    public function __construct(string $mailType, int $merchantId, array $data)
    {
        $this->mailType = $mailType;
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

        $markdown = match($this->mailType) {
            'price_update' => 'emails.merchants.price_update_completed',
            'product_push' => 'emails.merchants.product_push_completed',
            default => 'emails.merchants.price_update_completed',
        };
        $subject = match($this->mailType) {
            'price_update' => 'Products Price Update Request Completed for Merchant ID ' . $this->merchantId,
            'product_push' => 'Products Push Request Completed for Merchant ID ' . $this->merchantId,
            default => 'Job Completed for Merchant ID ' . $this->merchantId,
        };
        $markDownData = [
            'merchantId' => $this->merchantId,
            'link' => route('merchant_products.index', $queryStrings)
        ];
        if($this->mailType === 'price_update'){
            $markDownData['amount'] = $this->data['amount'] ?? 0;
            $markDownData['price_type'] = $this->data['price_type'] ?? 'flat';
        }
        return $this->subject($subject)
            ->markdown($markdown, $markDownData);
    }
}
