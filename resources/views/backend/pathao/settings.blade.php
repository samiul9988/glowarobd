@extends('backend.layouts.app')
@section('meta_title', 'Pathao Settings')
@section('content')

    <div class="row">
        <div class="col-12">
            <h2>Pathao Settings</h2>
            <span><strong>Integrated Pathao Shipping Automation Settings</strong></span>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-3">
            <h3>Integration Status</h3>
            <span><strong>Enable or disable automated Pathao shipping</strong></span>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-body d-flex justify-content-between">
                    <h3 class="mb-0 h6 text-center">
                        @if(@get_setting('automated_pathao_shipping') == 1)
                            {{ ('Automated Pathao shipping is Currently Enabled')}}
                        @else
                            {{ ('Automated Pathao shipping is Currently Disabled')}}
                        @endif
                    </h3>
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input value="1" type="checkbox" onchange="updateSettings(this, 'automated_pathao_shipping')" <?php if(@get_setting('automated_pathao_shipping') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-3">
            <h3>Match Shipping Area's</h3>
            <p><strong>Match Systems Shipping Area's With Pathao Shipping Area's</strong></p>
            <button id="match_pathao_areas" class="btn btn-success my-2">Generating Areas...</button>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h5>Generated or matched shipping area's bellow</h5>
                </div>
                <div class="card-body">
                    <div id="matched_shipping_areas">
                        <div class="skeleton-section">
                            <div class="row gutters-5">
                                @foreach (range(0,23) as $i)
                                    <div class="col-md-2">
                                        <div class="skeleton skeleton-item"></div>
                                        <div class="skeleton skeleton-item"></div>
                                        <div class="skeleton skeleton-item"></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function(){
            $('#match_pathao_areas').addClass('disabled').css('cursor', 'not-allowed');
            generateShippingAreas();
        });
        $('#match_pathao_areas').on('click', function(e){
            e.preventDefault();
            if(!$(this).hasClass('disabled')){
                generateShippingAreas();
            }
        });
        function showLoading(){
            $('#match_pathao_areas').addClass('disabled').css('cursor', 'not-allowed');
            $('#match_pathao_areas').text('Generating Areas...');
            $('#matched_shipping_areas').html(`
                <div class="skeleton-section">
                    <div class="row gutters-5">
                        @foreach (range(0,23) as $i)
                            <div class="col-md-2">
                                <div class="skeleton skeleton-item"></div>
                                <div class="skeleton skeleton-item"></div>
                                <div class="skeleton skeleton-item"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            `);
        }
    </script>
    <script type="text/javascript">
        let matchedPathaoAreas = [];
        let matchedSystemAreas = [];
        let unMatchedSystemAreas = [];
        function updateSettings(el, type){
            if($(el).is(':checked')){
                var value = 1;
            }
            else{
                var value = 0;
            }

            $.post('{{ route("business_settings.update.activation") }}', {_token:'{{ csrf_token() }}', type:type, value:value}, function(data){
                if(data == '1'){
                    AIZ.plugins.notify('success', '{{ ("Settings updated successfully") }}');
                    location.reload();
                }
                else{
                    AIZ.plugins.notify('danger', 'Something went wrong');
                }
            });
        }

        let elmCount = 0;
        function generateShippingAreas(){
            showLoading();
            $.get('{{ route("shipping.pathao.areas.generate") }}', {_token:'{{ csrf_token() }}'}, function(data){
                $('#match_pathao_areas').removeClass('disabled').css('cursor', 'pointer');
                $('#match_pathao_areas').text('Match/Generate Area\'s');
                $('#matched_shipping_areas').html(data.view);
                if(data.system_areas){
                    unMatchedSystemAreas = data.system_areas;
                }
                if(data.matched_areas){
                    data.matched_areas.map(area => {
                        matchedPathaoAreas.push(parseInt(area.pathao_area_id));
                        matchedSystemAreas.push(parseInt(area.system_area_id));
                        removeObjectWithId(unMatchedSystemAreas, parseInt(area.system_area_id));
                    })
                }
                data.areas?.map(area => {
                    addRow(event, area);
                });
                matchedPathaoAreas = [...new Set(matchedPathaoAreas)];
                matchedSystemAreas = [...new Set(matchedSystemAreas)];
                // console.log('before removed matchedSystemAreas', matchedSystemAreas);
                const generatedHTML = generateHTMLFromObjects(unMatchedSystemAreas);
            });
        }

        function generateHTMLFromObjects(unMatchedSystemAreas) {
            let html = '<div>Unmatched System Areas: ';

            for (const area of unMatchedSystemAreas) {
                html += `<button type="button" data-id="${area.id}" class="mr-1 mb-1" style="padding: 2px; font-size: 12px;border-radius: 4px;">${area.name}</button>`;
            }

            html += '</div>';

            $('#unmatched_system_areas').html(html);
        }

        function removeObjectWithId(arr, id) {
            const objWithIdIndex = arr.findIndex((obj) => obj.id === id);
            if (objWithIdIndex > -1) {
                arr.splice(objWithIdIndex, 1);
            }
            return arr;
        }

        function initSelect2(elmClass = 'e71-select2'){
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            var lastSearchTerm = '';
            $(`.${elmClass}`).each(function(){

                var $this = $(this);
                var initiatingElementId = $(this).data('type');
                var ajaxUrl;
                // Change the AJAX URL based on the initiatingElementId
                if (initiatingElementId === 'system') {
                    ajaxUrl = "{{ route('shipping.system.areas.search') }}"; // Custom search URL for element with ID "system"
                } else if (initiatingElementId === 'pathao') {
                    ajaxUrl = "{{ route('shipping.pathao.areas.search') }}"; // Custom search URL for element with ID "pathao_areas"
                } else {
                    ajaxUrl = "{{ route('shipping.system.areas.search') }}"; // Default search URL
                }

                if (!$($this).hasClass("select2-hidden-accessible")){
                    $this.select2({
                        ajax: {
                            url: ajaxUrl,
                            type: 'POST',
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    _token: CSRF_TOKEN,
                                    q: params.term, // search term
                                    page: params.current_page
                                };
                            },
                            processResults: function(data, params) {
                                params.current_page = data.current_page ? data.current_page + 1 : 1;
                                return {
                                    results: data.data,
                                    pagination: {
                                        more: (params.current_page * 10) < data.total
                                    }
                                };
                            },
                            autoWidth: false,
                            cache: true
                        },
                        placeholder: 'Type to search area',
                        minimumInputLength: 3,
                        maximumSelectionLength: (initiatingElementId === 'pathao') ? 1 : 25,
                        placeholder: 'Type to search area',
                        minimumInputLength: 3,
                        templateResult: formatArea,
                        templateSelection: formatAreaSelection,
                    });
                }

                function formatArea(area) {
                    if (area.loading) {
                        return area.area_name ? area.area_name : area.name;
                    }

                    var $container = $(
                        "<div class='select2-result-area clearfix'>" +
                        "<div class='select2-result-area__title'></div>" +
                        "</div>" +
                        "</div>" +
                        "</div>"
                    );

                    $container.find(".select2-result-area__title").text(area.area_name ? area.area_name : area.name);
                    $container.find(".select2-selection__choice__display").text(area.area_name ? area.area_name : area.name);
                    return $container;
                }

                function formatAreaSelection(area) {
                    var areaName = area.area_name ? area.area_name : area.name;
                    return areaName ? areaName : area.text;
                }

                $this.on('select2:selecting', function (e) {
                    var data = e.params.args.data;

                    let exist = false;
                    (initiatingElementId === 'pathao') ? exist = matchedPathaoAreas.includes(parseInt(data.area_id)) : exist = matchedSystemAreas.includes(parseInt(data.id));

                    if(exist){
                        AIZ.plugins.notify('danger', 'Area Already Selected');
                        e.preventDefault();
                    }
                });

                $this.on('select2:select', function (e) {
                    var data = e.params.data;
                    if(initiatingElementId === 'pathao'){
                        matchedPathaoAreas.push(data.id);
                    }else{
                        matchedSystemAreas.push(data.id);
                        unMatchedSystemAreas = removeObjectWithId(unMatchedSystemAreas, parseInt(data.id));
                        generateHTMLFromObjects(unMatchedSystemAreas);
                    }
                    // (initiatingElementId === 'pathao') ? matchedPathaoAreas.push(data.id) : matchedSystemAreas.push(data.id);
                });

                $this.on('select2:unselect', function (e) {
                    var data = e.params.data;
                    if(initiatingElementId === 'pathao'){
                        remove(data.id, 'pathao');
                    }else{
                        remove(data.id, 'system');
                        if(data.id != 'undefined'){
                            unMatchedSystemAreas.push({id: parseInt(data.id), name: data.text, status: 1});
                        }
                        generateHTMLFromObjects(unMatchedSystemAreas);
                    }
                    // (initiatingElementId === 'pathao') ? remove(data.id, 'pathao') : remove(data.id, 'system');
                });
            });
        }

        function addRow(e, values = '') {
            e.preventDefault();
            var table = document.getElementById("matched_table");
            var tBody = table.getElementsByTagName('tbody')[0];

            var row = document.createElement("tr");

            var systemColumn = document.createElement("td");

            systemColumn.innerHTML = `<div class="form-group row">
                <div class="col-12">
                    <select class="e71-select${elmCount} extra w-100" name="system_areas[${elmCount}][]" data-type="system" multiple="multiple">
                        ${(values && values.items.length != '' && values.items.map(item => {
                            return `<option selected value="${item.id}">${item.name}</option>`
                        }))}
                    </select>
                </div>
            </div>`;

            var pathaoColumn = document.createElement("td");
            pathaoColumn.innerHTML = `<div class="form-group row">
                <div class="col-12">
                    <select class="e71-select${elmCount}-1 w-100" name="pathao_areas[${elmCount}][]" data-type="pathao" multiple="multiple">
                        ${(values.length != '' && `<option selected value="${values.pathao_area_id}">${values.pathao_area.area_name}</option>`)}
                    </select>
                </div>
            </div>`;

            var removeButton = document.createElement('td');
            removeButton.innerHTML = `<div class="d-flex"><a href="javascript:void(0)" class="btn btn-soft-danger btn-icon btn-circle btn-sm remove" title="Delete">
                <i class="las la-trash"></i>
            </a><a href="javascript:void(0)" class="btn btn-soft-primary btn-icon btn-circle btn-sm update" title="Update This Row Only">
                <i class="las la-check-circle"></i>
            </a></div>`;

            // ${(values.length != '' && `<option selected value="${values.system_area_id}">${values.system_area.name}</option>`)}
            // ${(values.length != '' && `<option selected value="${values.pathao_area_id}">${values.pathao_area.area_name}</option>`)}

            row.appendChild(systemColumn);
            row.appendChild(pathaoColumn);
            row.appendChild(removeButton);

            tBody.appendChild(row);

            initSelect2(`e71-select${elmCount}`);
            initSelect2(`e71-select${elmCount}-1`);

            elmCount++;
        }

        function remove(index, type) {
            if(type === 'pathao'){
                matchedPathaoAreas = matchedPathaoAreas.filter(val => val !== parseInt(index));
            }else{
                matchedSystemAreas = matchedSystemAreas.filter(val => val !== parseInt(index));
            }
        }

        $(document).on('click','#matched_table tr td a.remove',function(e){
            e.preventDefault();
            // $(this).closest('tr').remove();
            var selectElems = $(this).closest('tr').find('select');
            var system_areas = [];
            var pathao_areas = [];
            if(selectElems.length > 1){
                Array.from(selectElems).map((selectElem, index) => {
                    if(index == 0){
                        system_areas = $(selectElem).val();
                    }else if(index == 1){
                        pathao_areas = $(selectElem).val()
                    }
                });
                if(system_areas && pathao_areas){
                    var formData = new FormData();
                    formData.append('system_areas', system_areas);
                    formData.append('pathao_areas', pathao_areas);
                    // Ajax post request
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        url: "{{route('shipping.matched.single.area.delete')}}",
                        type: 'POST',
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function(result, status, xhr) {
                            if(result && result.success){
                                AIZ.plugins.notify('success', result.message);
                            }else{
                                AIZ.plugins.notify('danger', result.message);
                            }
                        }
                    });
                    $(this).closest('tr').remove();
                }
            }else{
                AIZ.plugins.notify('danger', 'Both Pathao and System area should have atleast one selected');
                e.preventDefault();
            }
        });

        $(document).on('click','#matched_table tr td a.update',function(e){
            e.preventDefault();
            var selectElems = $(this).closest('tr').find('select');
            var system_areas = [];
            var pathao_areas = [];
            if(selectElems.length > 1){
                Array.from(selectElems).map((selectElem, index) => {
                    if(index == 0){
                        system_areas = $(selectElem).val();
                    }else if(index == 1){
                        pathao_areas = $(selectElem).val()
                    }
                });
                if(system_areas && pathao_areas){
                    var formData = new FormData();
                    formData.append('system_areas', system_areas);
                    formData.append('pathao_areas', pathao_areas);
                    // Ajax post request
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        url: "{{route('shipping.matched.single.area.save')}}",
                        type: 'POST',
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function(result, status, xhr) {
                            if(result && result.success){
                                AIZ.plugins.notify('success', result.message);
                            }else{
                                AIZ.plugins.notify('danger', result.message);
                            }
                        }
                    });

                }
            }else{
                AIZ.plugins.notify('danger', 'Both Pathao and System area should have atleast one selected');
                e.preventDefault();
            }
        });

        $(document).on('click', '.loadmore-paginator a', function(event){
            event.preventDefault();
            var page = $(this).attr('href').split('page=')[1];
            if(page === undefined){
                return false;
            }else{
                loadMoreMatchedAreas(page);
            }

        });

        function loadMoreMatchedAreas(page){
            $.get('{{ route("shipping.pathao.areas.generate") }}', {_token:'{{ csrf_token() }}', page: page}, function(data){
                if(data.nextPageUrl == null){
                    $(".loadmore-paginator a").text("No more area found").attr("href", "javascript:;").addClass("disabled");
                    // $(".loadmore-paginator a").attr("href", "javascript:;");
                    // $(".loadmore-paginator a").addClass("disabled");
                }else{
                    $(".loadmore-paginator a").attr("href", data.nextPageUrl);
                }
                if(data.matched_areas){
                    data.matched_areas.map(area => {
                        matchedPathaoAreas.push(area.pathao_area_id);
                        matchedSystemAreas.push(area.system_area_id);
                    })
                }
                data.areas?.map(area => {
                    addRow(event, area);
                });
                matchedPathaoAreas = [...new Set(matchedPathaoAreas)];
                matchedSystemAreas = [...new Set(matchedSystemAreas)];
            });
        }
    </script>
@endsection
