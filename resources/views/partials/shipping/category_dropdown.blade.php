@foreach($categories->where('parent_id', '!=', 0)->all() as $category)
    <option value="{{$category->id}}">{{ $category->getTranslation('name') }}</option>
@endforeach