<!-- View module swettings -->
<div class="modal fade" id="moduleSettingsViewModal_{{ $moduleSettingsId }}" tabindex="-1" role="dialog"
    aria-labelledby="moduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Module Settings Details</h5><button type="button" class="close"
                    data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">

                <div class="productModuleSettingsStatus_{{ $moduleSettingsId }}"></div>

                <form>
                    <div class="card">
                        <div class="card-header">
                            Email Settings
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="to_name">To Name(Supplier Name):</label>
                                    <input type="text" class="form-control" id="to_name_{{ $moduleSettingsId }}" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="to_email">To Email(Supplier Email):</label>
                                    <input type="email" class="form-control" id="to_email_{{ $moduleSettingsId }}" readonly>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="bcc_name">Bcc Name:</label>
                                    <input type="text" class="form-control" id="bcc_name_{{ $moduleSettingsId }}" maxlength="150">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="bcc_email">Bcc Email:</label>
                                    <input type="email" class="form-control" id="bcc_email_{{ $moduleSettingsId }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email_subject">Email Subject:</label>
                                <input type="text" class="form-control" id="email_subject_{{ $moduleSettingsId }}" maxlength="200">
                            </div>

                            <div class="form-group">
                                <label for="email_body">Email Body:</label>
                                <textarea class="form-control" id="email_body_{{ $moduleSettingsId }}" rows="3"></textarea>
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        id="activate_delivery_note_shipping_{{ $moduleSettingsId }}">
                                    <label class="form-check-label" for="activate_delivery_note_shipping">
                                        Activate delivery note shipping
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="activate_customer_data_sending_{{ $moduleSettingsId }}">
                                    <label class="form-check-label" for="activate_customer_data_sending">
                                        Activate customer data sending
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        id="enable_delivery_address_data_shipping_{{ $moduleSettingsId }}">
                                    <label class="form-check-label" for="enable_delivery_address_data_shipping">
                                        Enable delivery address data shipping
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            Cron Settings
                        </div>
                        <div class="card-body">

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="max_error">Setting maximum error limit:</label>
                                    <input type="number" class="form-control" id="max_error_{{ $moduleSettingsId }}" maxlength="3">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="call_count">How many times cron job called:</label>
                                    <input type="text" class="form-control" id="call_count_{{ $moduleSettingsId }}" readonly>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="error_count">How many times cron job failed:</label>
                                    <input type="number" class="form-control" id="error_count_{{ $moduleSettingsId }}" readonly>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="last_error">When was cron job failed:</label>
                                    <input type="text" class="form-control" id="last_error_{{ $moduleSettingsId }}" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="last_call">When was cron job last called:</label>
                                    <input type="text" class="form-control" id="last_call_{{ $moduleSettingsId }}" readonly>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            Order & Delivery Settings
                        </div>
                        <div class="card-body">

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="delivery_status">Delivery status:</label>
                                    <select class="form-control" id="delivery_status_{{ $moduleSettingsId }}">
                                        <option>Choose Status</option>
                                        <option value="0">Not Active</option>
                                        <option value="1">Active</option>
                                        <option value="2">Waiting</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="order_in_logistics_{{ $moduleSettingsId }}">
                                        <label class="form-check-label" for="order_in_logistics">
                                            Place order as set order in logistics
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="order_shipped_{{ $moduleSettingsId }}">
                                        <label class="form-check-label" for="order_shipped">
                                            Declare order as shipped
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            MOD Settings
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="wait_mod_no">Wait until the MOD pointer number is reached:</label>
                                    <input type="number" class="form-control" id="wait_mod_no_{{ $moduleSettingsId }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="wait_mod_id">Wait until MOD has successfully completed with ID:</label>
                                    <input type="number" class="form-control" id="wait_mod_id_{{ $moduleSettingsId }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            </div>

        </div>
    </div>
</div>
