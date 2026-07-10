@forelse ($products as $key => $item)
    <tr>
        <td>
            {{ $key + 1 }}
        </td>
        <td>
            {{ @$item->product->name }}
            @if (($item->variation != '') & ($item->variation != null))
                ({{ @$item->variation }})
            @endif
        </td>
        <td class="text-right">
            {{ @$item->total_quantity }}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="3" class="text-center">
            {{ ('No data found') }}
        </td>
    </tr>
@endforelse
