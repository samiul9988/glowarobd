<style>
    #sample_content {
        width: 100%;
        overflow-x: auto;
        overflow-y: auto;
        background: #f5f5f5;
        padding: 20px;
    }

    #sample_content > div {
        transform-origin: top left;
        margin: 0 auto;
        background: #fff;
    }

    /* Responsive preview scaling */
    @media (max-width: 1400px) {
        #sample_content > div {
            transform: scale(0.9);
        }
    }

    @media (max-width: 1200px) {
        #sample_content > div {
            transform: scale(0.8);
        }
    }

    @media (max-width: 992px) {
        #sample_content > div {
            transform: scale(0.7);
        }
    }

    @media (max-width: 768px) {
        #sample_content > div {
            transform: scale(0.6);
        }
    }
</style>
{{-- sample code view modal --}}
<div class="modal fade" id="sample_view_code" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h6 class="modal-title" id="exampleModalLabel">Short code & Sample template</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                {{-- short code variable --}}
                <div class="card">
                    <div class="card-header px-2">
                        <h6 class="fs-15">Short Code Variable</h6>
                    </div>
                    <div class="card-body" id="short_code_variables"></div>
                </div>

                {{-- sample code --}}
                <div class="card">
                    <div class="card-header px-2">
                        <h6 class="fs-16">Sample Template</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-right">
                            <button type="button" class="btn btn-sm btn-soft-success" id="copy_code" title="Copy">
                                <i class="las la-copy"></i>
                            </button>
                        </div>
                        <div class="content_body mt-2" id="sample_content"></div>
                    </div>
                </div>
            </div>


            <div class="modal-footer text-right">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>
