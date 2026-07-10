@foreach($brands as $brand)
<option value="{{$brand->id}}">{{ $brand->getTranslation('name') }}</option>
@endforeach