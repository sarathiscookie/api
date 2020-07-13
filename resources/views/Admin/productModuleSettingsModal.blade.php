<!-- Module settings Modal -->
@isset($moduleSetting)
    <div class="modal fade" id="moduleSettingsModal_{{ $moduleSetting->id }}" tabindex="-1" role="dialog"
        aria-labelledby="moduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Module Settings</h5><button type="button" class="close" data-dismiss="modal"
                        aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>

                <div class="modal-body">

                    <div class="productModuleSettingsStatus_{{ $moduleSetting->id }}"></div>

                    <div class="moduleSettingsValidationAlert_{{ $moduleSetting->id }}"></div>

                    <form>

                        @csrf

                        <div class="card mt-3">

                            <div class="card-header">
                                Email Settings
                            </div>

                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group col-md-6">

                                        <label for="supplier">Supplier Name <span class="required">*</span></label>

                                        <select class="form-control" id="supplier_{{ $moduleSetting->id }}" name="supplier">
                                            <option value="">Choose Suppliers</option>
                                            @isset($suppliers)
                                                @foreach($suppliers as $supplier)
                                                    
                                                    <option value="{{ $supplier->supplierId }}" @if( $supplier->supplierId === $moduleSetting->user_supplier_id) selected="selected" @endif>{{ ucwords($supplier->supplierName) }} ( {{$supplier->supplierEmail}} )</option>

                                                @endforeach
                                            @endisset
                                        </select>

                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">

                                        <label for="bcc_name">Bcc Name <span class="required">*</span></label>

                                        <input type="text" name="bcc_name" class="form-control" id="bcc_name_{{ $moduleSetting->id }}" maxlength="150" value="{{ $moduleSetting->mail_bcc_name }}">

                                    </div>
                                    <div class="form-group col-md-6">

                                        <label for="bcc_email">Bcc Email <span class="required">*</span></label>

                                        <input type="email" name="bcc_email" class="form-control" id="bcc_email_{{ $moduleSetting->id }}" value="{{ $moduleSetting->mail_bcc }}">

                                    </div>
                                </div>

                                <div class="form-group">

                                    <label for="email_subject">Email Subject <span class="required">*</span></label>

                                    <input type="text" name="email_subject" class="form-control" id="email_subject_{{ $moduleSetting->id }}" maxlength="200" value="{{ $moduleSetting->mail_subject }}">

                                </div>

                                <div class="form-group">

                                    <label for="email_body">Email Body <span class="required">*</span></label>

                                    <textarea name="email_body" class="form-control" id="email_body_{{ $moduleSetting->id }}" rows="3">{{ $moduleSetting->mail_body }}</textarea>
                                    
                                </div>

                                <div class="form-group">
                                    <div class="form-check">

                                        <input class="form-check-input" type="checkbox"
                                            id="activate_delivery_note_shipping_{{ $moduleSetting->id }}" name="activate_delivery_note_shipping" @if( $moduleSetting->setOrderShipped === 1) checked @endif>

                                        <label class="form-check-label" for="activate_delivery_note_shipping">
                                            Set Order Shipped
                                        </label>

                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="form-check">

                                        <input class="form-check-input" type="checkbox"
                                            id="activate_customer_data_sending_{{ $moduleSetting->id }}" name="activate_customer_data_sending" @if( $moduleSetting->setOrderLogistic === 1) checked @endif>

                                        <label class="form-check-label" for="activate_customer_data_sending">
                                            Set Order Logistic
                                        </label>

                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="form-check">

                                        <input class="form-check-input" type="checkbox"
                                            id="enable_delivery_address_data_shipping_{{ $moduleSetting->id }}" name="enable_delivery_address_data_shipping" @if( $moduleSetting->getOrderDeliveryNote === 1) checked @endif>

                                        <label class="form-check-label" for="enable_delivery_address_data_shipping">
                                            Get Order Delivery Note
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

                                        <label for="max_error">Setting maximum error limit</label>

                                        <input type="number" class="form-control" id="max_error_{{ $moduleSetting->id }}" name="max_error" value="{{ $moduleSetting->max_error }}">

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

                                        <label for="delivery_status">Delivery Status</label>

                                        <select class="form-control" id="delivery_status_{{ $moduleSetting->id }}" name="delivery_status">
                                            <option value="">Choose Status</option>
                                            @isset($deliveryStatus)
                                                @foreach($deliveryStatus as $key => $status)
                                                    <option value="{{ $key }}" @if( $key === $moduleSetting->delivery_status) selected="selected" @endif>{{ $status }}</option>
                                                @endforeach
                                            @endisset
                                        </select>

                                    </div>

                                    <div class="form-group col-md-4">
                                        <div class="form-check">

                                            <input class="form-check-input" type="checkbox" id="order_in_logistics_{{ $moduleSetting->id }}" name="order_in_logistics" @if( $moduleSetting->order_in_logistics === 1) checked @endif>

                                            <label class="form-check-label" for="order_in_logistics">
                                                Place order as set order in logistics
                                            </label>

                                        </div>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <div class="form-check">

                                            <input class="form-check-input" type="checkbox" id="order_shipped_{{ $moduleSetting->id }}" name="order_shipped" @if( $moduleSetting->order_shipped === 1) checked @endif>

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

                                        <label for="wait_mod_no">Wait until the MOD pointer number is reached</label>

                                        <input type="number" class="form-control" id="wait_mod_no_{{ $moduleSetting->id }}" name="wait_mod_no" value="{{ $moduleSetting->wait_mod_no }}">

                                    </div>
                                    <div class="form-group col-md-6">

                                        <label for="wait_mod_id">Wait until MOD has successfully completed with
                                            ID</label>

                                        <input type="number" class="form-control" id="wait_mod_id_{{ $moduleSetting->id }}" name="wait_mod_id" value="{{ $moduleSetting->wait_mod_id }}">

                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary saveModuleSettings" data-modulesettingsid="{{ $moduleSetting->id }}">Save Settings</button>
                </div>

            </div>
        </div>
    </div>
@endisset
