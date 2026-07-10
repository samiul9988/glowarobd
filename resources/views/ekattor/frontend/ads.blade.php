<section class="ads_section">
    <div class="container my-2">
        <div class="row">
            @foreach ($ads as $ad)
                @php
                    if($ad->link_type == 'product'):
                        $link = route('product', $ad->product->slug);

                    elseif ($ad->link_type == 'category'):
                        $link = route('products.category', $ad->category->slug);

                    elseif ($ad->link_type == 'brand'):
                        $link = route('products.brand', $ad->brand->slug);

                    elseif ($ad->link_type == 'tag'):
                        $link = route('products.tags',$ad->link);

                    elseif ($ad->link_type == 'custom'):
                        $link = $ad->link;
                        
                    else:
                        $link = '#';
                    endif;
                @endphp

                <div class="col-md-6 mb-3">
                    @if($ad->image != null)
                        <a href="{{$link}}" target="_blank">
                            <img src="{{ uploaded_asset($ad->image) }}" alt="{{translate('Image')}}" class="ad-image">
                        </a>
                    @else
                        @if($ad->code != null)
                            <div class="ad-image text-center">
                                {!! $ad->code !!}
                            </div>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
<style> 
    .ad-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      cursor: pointer;
    }
</style>
