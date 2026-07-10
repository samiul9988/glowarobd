@php
    $categoryfilePath = storage_path('app/public/categories/category.json');
    if (file_exists($categoryfilePath)) {
        // dd('Fetching from json');
        $jsonData = file_get_contents($categoryfilePath);
        $categories = collect(json_decode($jsonData, true));
        if ($categories->isEmpty()) {
            // dd('Fetching from database');
            $categories = collect(\App\Models\Category::all()->toArray());
            if(!file_exists(storage_path('app/public/categories'))){
                mkdir(storage_path('app/public/categories'), 0775, true);
            }
            file_put_contents($categoryfilePath, $categories->toJson());
        }
    } else {
        // dd('Fetching from database and store as json');
        $categories = collect(\App\Models\Category::all()->toArray());
        if(!file_exists(storage_path('app/public/categories'))){
            mkdir(storage_path('app/public/categories'), 0775, true);
        }
        file_put_contents($categoryfilePath, $categories->toJson());
    }
    // $collection_designs = collect(\App\Models\CollectionDesign::all()->toArray());
@endphp
@if (get_setting('home_categories') != null)
    @php
        $home_categories = json_decode(get_setting('home_categories'));
    @endphp
    @foreach ($home_categories as $key => $value)
        @php
            // $category = (object) $categories->where('id', $value)->first();
            $category = (object) $categories->where('id', $value->cid)->first();
            $design = (object) $collection_designs->where('id', $value->did)->first();
        @endphp
        @include(config('app.theme') . 'frontend.components.collection_design.' . $design->file_name, [
            'category' => $category,
            'type' => 'home',
        ]);
    @endforeach
@endif
