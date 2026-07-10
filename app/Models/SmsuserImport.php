<?php

namespace App\Models;

use App\Models\Smsuser;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;

class SmsuserImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $oldnumber = Smsuser::where('mobile_number',$row['mobile_number'])->first();
        if(!$oldnumber):
        return new Smsuser([
           'mobile_number' => $row['mobile_number']
        ]);
        endif;
    }
}
