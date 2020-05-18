<!-- Add module settings -->
@isset($moduleSettingsId)
    <div class="modal fade" id="moduleSettingsModal_{{ $moduleSettingsId }}" tabindex="-1" role="dialog"
        aria-labelledby="moduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Module Settings {{ $moduleSettingsId }}</h5><button type="button" class="close" data-dismiss="modal"
                        aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">

                    <div class="productModuleSettingsStatus_{{ $moduleSettingsId }}"></div>

                    <form>
                        <div class="card mt-3">
                            <div class="card-header">
                                Email Settings
                            </div>
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="supplier">Supplier Name <span class="required">*</span></label>
                                        <select class="form-control" id="supplier" name="supplier">
                                            @isset($suppliers)
                                                @forelse($suppliers as $supplier)
                                                    <option value="{{ $supplier->supplierId }}">{{ ucwords($supplier->supplierName) }} ( {{$supplier->supplierEmail}} )</option>
                                                @empty
                                                    <option>No suppliers</option>
                                                @endforelse
                                            @endisset
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="bcc_name">Bcc Name <span class="required">*</span></label>
                                        <input type="text" name="bcc_name" class="form-control" id="bcc_name" maxlength="150">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="bcc_email">Bcc Email <span class="required">*</span></label>
                                        <input type="email" name="bcc_email" class="form-control" id="bcc_email">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="email_subject">Email Subject <span class="required">*</span></label>
                                    <input type="text" name="email_subject" class="form-control" id="email_subject" maxlength="200">
                                </div>

                                <div class="form-group">
                                    <label for="email_body">Email Body <span class="required">*</span></label>
                                    <textarea name="email_body" class="form-control" id="email_body" rows="3"></textarea>
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            id="activate_delivery_note_shipping">
                                        <label class="form-check-label" for="activate_delivery_note_shipping">
                                            Activate delivery note shipping
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            id="activate_customer_data_sending">
                                        <label class="form-check-label" for="activate_customer_data_sending">
                                            Activate customer data sending
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            id="enable_delivery_address_data_shipping">
                                        <label class="form-check-label" for="enable_delivery_address_data_shipping">
                                            Enable delivery address data shipping
                                        </label>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                Cron Settings
                            </div>
                            <div class="card-body">

                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="max_error">Setting maximum error limit <span class="required">*</span></label>
                                        <input type="number" class="form-control" id="max_error" maxlength="3">
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                Order & Delivery Settings
                            </div>
                            <div class="card-body">

                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="delivery_status">Delivery status <span class="required">*</span></label>
                                        <select class="form-control" id="delivery_status">
                                            <option>Choose Status</option>
                                            <option value="0">Not Active</option>
                                            <option value="1">Active</option>
                                            <option value="2">Waiting</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="order_in_logistics">
                                            <label class="form-check-label" for="order_in_logistics">
                                                Place order as set order in logistics
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="order_shipped">
                                            <label class="form-check-label" for="order_shipped">
                                                Declare order as shipped
                                            </label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                MOD Settings
                            </div>
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="wait_mod_no">Wait until the MOD pointer number is reached <span class="required">*</span></label>
                                        <input type="number" class="form-control" id="wait_mod_no">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="wait_mod_id">Wait until MOD has successfully completed with
                                            ID <span class="required">*</span></label>
                                        <input type="number" class="form-control" id="wait_mod_id">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
                <div class="modal-footer"><button type="button" class="btn btn-primary saveModuleDetails"
                        data-addmodulesettingsid="{{ $moduleSettingsId }}">Update</button></div>
            </div>
        </div>
    </div>
@endisset
