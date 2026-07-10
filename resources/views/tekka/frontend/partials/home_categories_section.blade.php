@php
    $categoryfilePath = storage_path('app/public/categories/category.json');
    if (file_exists($categoryfilePath)) {
        $jsonData = file_get_contents($categoryfilePath);
        $categories = collect(json_decode($jsonData, true));
        if ($categories->isEmpty()) {
            $categories = collect(\App\Models\Category::all()->toArray());
        }
    } else {
        $categories = collect(\App\Models\Category::all()->toArray());
    }
    $collection_designs = collect(\App\Models\CollectionDesign::all()->toArray());
@endphp
@if (get_setting('home_categories') != null)
    @php
        $home_categories = json_decode(get_setting('home_categories'));
        // dd($home_categories);
    @endphp
    @foreach ($home_categories as $key => $value)
        @php
            $category = (object) $categories->where('id', $value->cid)->first();
            $design = (object) $collection_designs->where('id', $value->did)->first();
            // dd($design);
        @endphp
        <section class="mb-3">
            {{-- <h6>{{ $design->file_name }}</h6> --}}
            @include(config('app.theme') . 'frontend.components.collection_design.' . $design->file_name, [
                'category' => $category,
            ])

        </section>
    @endforeach
@endif
