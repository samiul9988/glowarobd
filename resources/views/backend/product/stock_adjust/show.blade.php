@extends('backend.layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h1 class="h2 fs-16 mb-0">{{ ('Stock Adjust Details') }}</h1>
    </div>
    <div class="card-body">


        <div class="row gutters-5 mt-2">
            <div class="col text-center text-md-left">
                <h5>Seller</h5>
                <address>
                    <strong class="text-main">{{@$stock_adjust->sellername->name}}</strong><br>
                    <a href="tel:">{{@$stock_adjust->sellername->phone}}</a>
                    <p>{{@$stock_adjust->sellername->address}}</p>
                </address>
            </div>

            <div class="col ml-auto">
                <table class="float-right">
                    <tbody>
                        <tr>
                            <td class="text-main text-bold px-2">Stock Adjust #</td>
                            <td class="text-right text-info text-bold mx-2">{{@$stock_adjust->sa_number}}</td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold px-2">Date:</td>
                            <td class="text-right text-bold mx-2">{{date('m-d-Y', @$stock_adjust->sa_date)}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <span class="text-capitalize w-auto text-white fw-900 badge rounded-pill bg-secondary">Reason: {{@$stock_adjust->sa_type}}</span>


        <hr class="new-section-sm bord-no">

        <div class="row">
            <div class="col-lg-12 table-responsive">
                <table class="table table-bordered invoice-summary">
                    <thead>
                        <tr class="bg-trans-dark">
                            <th data-breakpoints="lg" class="min-col">#</th>
                            <th width="10%">{{ ('Photo')}}</th>
                            <th class="text-uppercase">{{ ('Description')}}</th>
                            <th data-breakpoints="lg" class="min-col text-right text-uppercase">{{ ('Variant')}}</th>
                            <th data-breakpoints="lg" class="min-col text-right text-uppercase">{{ ('SKU')}}</th>
                            <th data-breakpoints="lg" class="min-col text-center text-uppercase">{{ ('Qty')}}</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($stock_adjust->stockAdjustDetails as $key => $stock_adjust_item)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td><img src="{{ uploaded_asset($stock_adjust_item->product->thumbnail_img)}}" alt="Image" class="size-50px img-fit"></td>
                                <td>{{$stock_adjust_item->product->name }}</td>
                                <td class="text-center">
                                    @if(@$stock_adjust_item->product_stock->variant != null)
                                        ({{@$stock_adjust_item->product_stock->variant }})
                                    @else
                                        {{ ('N/A')}}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(@$stock_adjust_item->product_stock->sku != null)
                                        ({{@$stock_adjust_item->product_stock->sku }})
                                    @else
                                        {{ ('N/A')}}
                                    @endif
                                </td>
                                <td class="text-right" >{{ $stock_adjust_item->qty }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5">{{ ('Total Quantity')}} :</th>
                            <th class="text-right">{{ $stock_adjust->stockAdjustDetails->sum('qty') }}</th>
                        </tr>
                    </tfoot>
                </table>
                <p><strong>Note:</strong> {{$stock_adjust->note}}</p>
                <p><strong>Attachments:</strong></p>
            </div>
            @if($stock_adjust->sa_type == 'damage' && $stock_adjust->attachments != null)
                <div class="col-12 d-flex justify-content-start flex-wrap gap-2">
                    @php
                        $photos = explode(',', $stock_adjust->attachments);
                    @endphp
                    @foreach ($photos as $key => $photo)
                        <div class="shadow shadow-sm p-2 {{ $loop->last ? '' : 'mr-2' }}">
                            <img src="{{ uploaded_asset($photo) }}" class="h-200px w-100px h-md-150px w-md-100px" style="object-fit: contain;">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
