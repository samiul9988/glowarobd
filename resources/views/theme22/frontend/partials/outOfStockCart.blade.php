<div class="modal-body p-4 added-to-cart">
    <div class="text-center text-danger">
        <h2>{{ ('oops..')}}</h2>
        @if($outofstockmsg)
        <h3>{{ ('Maximum ')}} {{ $quantity }} {{ ('quuantity can be added for single order!')}}</h3>
        @else
        <h3>{{ ('This item is out of stock!')}}</h3>
        @endif
    </div>
    <div class="text-center mt-5">
        <button class="btn btn-outline-primary" data-dismiss="modal">{{ ('Back to shopping')}}</button>
    </div>
</div>
