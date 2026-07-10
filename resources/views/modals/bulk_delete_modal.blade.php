<!-- delete Modal -->
<div id="bulk_delete-modal" class="modal fade">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ ('Delete Confirmation')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mt-1">{{ ('Are you sure to delete this?')}}</p>
                <button type="button" class="btn btn-link mt-2" data-dismiss="modal">{{ ('Cancel')}}</button>
                <a href="javascript:;" id="" class="btn btn-primary mt-2" onclick="bulk_delete()">{{ ('Delete')}}</a>
            </div>
        </div>
    </div>
</div><!-- /.modal -->
