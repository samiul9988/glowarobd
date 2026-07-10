<?php

namespace App\Imports;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProductsImport implements ToCollection
{
    public $productNames = [];

    public function collection(Collection $rows)
    {
        // Skip the header row (row 1) and process from row 2
        foreach ($rows->skip(1) as $row) {
            if (!empty($row[2])) { // Assuming Name is in the 3rd column (index 2)
                $this->productNames[] = Str::slug($row[2]);
            }
        }
    }
}
