<div id="unmatched_system_areas">

</div>
<form action="{{ route('shipping.matched.areas.save') }}" method="post">
@csrf
<div class="table-responsive">
<table id="matched_table" class="table table-bordered">
  <thead>
    <tr>
      <th style="width: 47%">System Area</th>
      <th style="width: 47%">Pathao Area</th>
      <th style="width: 6%"></th>
    </tr>
  </thead>
  <tbody>
    
  </tbody>
</table>
</div>
<div class="loadmore-paginator d-flex justify-content-center">
  @if(isset($nextPageUrl))
  <a href="{{ $nextPageUrl }}" class="btn btn-sm btn-soft-primary text-center">Load More Matched Area</a>
  @else
  <a href="javascript:;" class="btn btn-sm btn-soft-primary text-center disabled">No More Matched Area</a>
  @endif
</div>
<div class="d-flex justify-content-between">
    <button onclick="addRow(event)" class="btn btn-dark">Add Area</button>
    {{-- <button type="submit" class="btn btn-sm btn-primary">Save Areas</button> --}}
</div>
</form>