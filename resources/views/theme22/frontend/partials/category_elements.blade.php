<div class="card-columns">
    @foreach (\App\Utility\CategoryUtility::get_immediate_children_ids($category->id) as $key => $first_level_id)
    @php
        $first_children_uni = cache()->remember('first_uni_immediate_children_ids_'.$first_level_id, 86400, function () use ($first_level_id) {
            return \App\Models\Category::find($first_level_id);
        });
    @endphp
        <div class="card shadow-none border-0"> 
            <ul class="list-unstyled mb-3">
                <li class="fw-600 border-bottom pb-2 mb-3">
                    <a class="text-reset" href="{{ route('products.category', $first_children_uni->slug) }}">{{ $first_children_uni->getTranslation('name') }}</a>
                </li>
                @foreach (\App\Utility\CategoryUtility::get_immediate_children_ids($first_level_id) as $key => $second_level_id)
                @php
                    $second_children_uni = cache()->remember('second_uni_immediate_children_ids_'.$second_level_id, 86400, function () use ($second_level_id) {
                        return \App\Models\Category::find($second_level_id);
                    });
                @endphp
                    <li class="mb-2">
                        <a class="text-reset" href="{{ route('products.category', $second_children_uni->slug) }}">{{ $second_children_uni->getTranslation('name') }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
