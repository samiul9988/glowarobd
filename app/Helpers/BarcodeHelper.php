<?php

namespace App\Helpers;
use Carbon\Carbon;
use App\Models\Barcode;

class BarcodeHelper
{
    public static function generate(int $productId, int $purchaseOrderItemId, string $variant, string|Carbon $expireDate): string
    {
        if ($expireDate instanceof Carbon) {
            $expireDate = $expireDate->format('Ymd');
        } else {
            $expireDate = Carbon::parse($expireDate)->format('Ymd');
        }

        return "{$productId}-{$purchaseOrderItemId}-{$variant}-{$expireDate}";
    }

    public static function unique()
    {
        do {
            $barcode = time() . rand(10, 999); // 12-13 digits
        } while (Barcode::where('code', $barcode)->exists());

        return $barcode;
    }

    public static function decode(string $barcode): ?array
    {
        if(count(explode('-', $barcode)) < 4) {
            return null; // Invalid barcode format
        }

        [$product_id, $purchase_order_item_id, $variant, $expire_date] = explode('-', $barcode);

        return [
            'product_id' => (int) $product_id,
            'purchase_order_item_id' => (int) $purchase_order_item_id,
            'variant' => $variant,
            'expire_date' => Carbon::createFromFormat('Ymd', $expire_date)->format('Y-m-d')
        ];
    }

    public static function render(string $barcode, bool $includeText = false): string
    {
        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $barcodeText = '';
        if ($includeText) {
            $barcodeText = '<div style="font-size: 8px;margin-top: 3px;letter-spacing: 2px;">' . htmlspecialchars($barcode) . '</div>';
        }

        return '
            <div style="text-align:center;">
                <img src="data:image/png;base64,' . base64_encode($generator->getBarcode($barcode, $generator::TYPE_CODE_128, 1)) . '" width="90" height="25" alt="Barcode">
                ' . $barcodeText . '
            </div>';
    }

}
