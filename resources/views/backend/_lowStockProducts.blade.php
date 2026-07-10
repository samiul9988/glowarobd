@foreach($data as $key => $item)
    <tr>
        <td>
            {{$data->firstItem() + $key}}
        </td>
        <td>
            {{@$item->name}}
            @if($item->variant != '' & $item->variant != NULL)
                ({{$item->variant}})
            @endif
        </td>
        <td class="text-right">
            {{$item->qty}}
        </td>
    </tr>
@endforeach

