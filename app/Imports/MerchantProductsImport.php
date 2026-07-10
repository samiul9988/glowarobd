<?php
namespace App\Imports;

use App\Models\MerchantProduct;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class MerchantProductsImport implements ToCollection, WithChunkReading, WithHeadingRow, ShouldQueue
{
    protected $merchantId;

    public function __construct(int $merchantId)
    {
        $this->merchantId = $merchantId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                $productId = isset($row['sku']) ? explode('-', $row['sku'])[0] : null;
                $lastPrice = $row['mrp'] ?? null;

                if ($productId && $lastPrice) {
                    MerchantProduct::updateOrCreate(
                        ['merchant_id' => $this->merchantId, 'product_id'  => $productId],
                        ['last_price'  => $lastPrice]
                    );
                }
            } catch (\Exception $e) {
                Log::channel('merchant')->error("❌ ERROR: Failed to import row: " . $e->getMessage());
            }
        }
    }

    /**
     * Define chunk size (rows per job)
     */
    public function chunkSize(): int
    {
        return 500; // adjust based on memory
    }
}
