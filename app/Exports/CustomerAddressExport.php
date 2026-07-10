<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CustomerAddressExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        return \App\Models\User::query()
            ->select([
                'users.name as name',
                'users.email as email',
                'users.phone as phone',
                'addresses.address as address',
                'areas.name as area',
                'cities.name as city',
                'states.name as state',
                'countries.name as country'
            ])
            ->leftJoin('addresses', 'users.id', '=', 'addresses.user_id')
            ->leftJoin('areas', 'addresses.area_id', '=', 'areas.id')
            ->leftJoin('cities', 'addresses.city_id', '=', 'cities.id')
            ->leftJoin('states', 'addresses.state_id', '=', 'states.id')
            ->leftJoin('countries', 'addresses.country_id', '=', 'countries.id')
            ->where('users.user_type', 'customer');
            // ->orderBy('users.name');
    }

    public function headings(): array
    {
        return [
            'Customer',
            'Email',
            'Phone',
            'Address',
            'Area',
            'City',
            'State',
            'Country'
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->name,
            $customer->email,
            $customer->phone,
            $customer->address ?? 'N/A',
            $customer->area ?? 'N/A',
            $customer->city ?? 'N/A',
            $customer->state ?? 'N/A',
            $customer->country ?? 'N/A'
        ];
    }
}