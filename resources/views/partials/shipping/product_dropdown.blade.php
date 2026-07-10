@foreach($products as $product)
    <option value="{{$product->id}}">{{ $product->getTranslation('name') }}</option>
@endforeach