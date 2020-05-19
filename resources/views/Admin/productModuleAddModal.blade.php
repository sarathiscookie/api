<!-- Add module -->
<div class="modal fade" id="moduleModal_{{ $productApiId }}" tabindex="-1" role="dialog"
    aria-labelledby="moduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Module</h5><button type="button" class="close" data-dismiss="modal"
                    aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">

                <div class="addModuleSettingsStatus_{{ $productApiId }}"></div>
                <form>
                    <div class="form-group">
                        <label for="module">Module:</label>
                        <select class="form-control" id="module_id_{{ $productApiId }}">
                            <option>Choose Module</option>
                            {!! $moduleOptions !!}
                        </select>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary saveModuleDetails" data-addmoduleproductid="{{ $productApiId }}">Add Module</button>
            </div>
        </div>
    </div>
</div>
