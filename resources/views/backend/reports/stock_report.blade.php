@extends('backend.layouts.app')
@php
    $categories = Cache::remember('filter_categories', now()->addDay(), function () {
        return \App\Models\Category::pluck('name', 'id')->toArray();
    });
@endphp
@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class=" align-items-center">
       <h1 class="h3">{{ ('Product wise current stock report')}}</h1>
	</div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <!--card body-->
            <div class="card-body">
                <form action="{{ route('stock_report.index') }}" method="GET">
                    <div class="form-group row offset-lg-2">
                        <label class="col-md-3 col-form-label">{{ ('Sort by Category')}} :</label>
                        <div class="col-md-5">
                            <select id="demo-ease" class="from-control aiz-selectpicker" name="category_id" required>
                                @foreach ($categories as $id => $name)
                                    <option value="{{ $id }}" @if ($id == request('category_id')) selected @endif>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary" type="submit">{{ ('Filter') }}</button>
                        </div>
                    </div>
                </form>
                <table class="table table-bordered aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ ('Product Name') }}</th>
                            <th>{{ ('Stock') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $key => $product)
                            @php
                                $qty = 0;
                                foreach ($product->stocks as $key => $stock) {
                                    $qty += $stock->qty;
                                }
                            @endphp
                            <tr>
                                <td>{{ $product->getTranslation('name') }}</td>
                                <td>{{ $qty }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination mt-4">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
