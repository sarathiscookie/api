@extends('admin.layouts.app')

@section('title', 'Shop List')

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-list-alt"></i> Shop List</li>
        </ol>
      </nav>

      <div class="card border-primary">
        <div class="card-header bg-primary">
          Shop List
        </div>

        <div class="card-body">
          <div class="text-right">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createShopModal"><i class="fas fa-plus"></i> Create Shop</button>
            <hr>
          </div>
          
          <div class="table-responsive">

            <div class="responseShopMessage"></div>

            <table id="shop_list" class="table table-bordered table-hover display" style="width:100%">
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th>Shop Name</th>
                  <th>Active</th>
                  <th>Actions</th>
                </tr>
              </thead>

              <tbody></tbody>

              <tfoot>
                <td></td>
                <th><input type="text" id="1"  class="form-control input-sm search-input" placeholder="Search Shop"></th>
                <td>
                  <select class="form-control input-sm search-input" id="2">
                    <option value="">All</option>
                    <option value="yes">Active</option>
                    <option value="no">Not Active</option>
                  </select>
                </td>
                <td></td>
              </tfoot>

            </table>

            <!-- Export buttons are append here -->
            <div id="shopButtons"></div>

          </div>
        </div>
      </div>
  
      <!-- Create hop modal -->
      <div class="modal fade" id="createShopModal" tabindex="-1" role="dialog" aria-labelledby="createShopModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="createShopModalLabel">Create Shop</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">

              <div class="shopValidationAlert"></div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="shop">Shop <span class="required">*</span></label>
                  <input id="shop" type="text" class="form-control" name="shop" autocomplete="shop" maxlength="150" autofocus>

                </div>
                <div class="form-group col-md-6">
                  <label for="company">Company <span class="required">*</span></label>
                  <select id="company" class="form-control" name="company">
                    <option value="">Choose...</option>
                    <option value="de">company</option>
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="mail_driver">Mail Driver <span class="required">*</span></label>
                  <input id="mail_driver" type="text" class="form-control" name="mail_driver" maxlength="150" autocomplete="mail_driver">
                </div>

                <div class="form-group col-md-4">
                  <label for="mail_port">Mail Port <span class="required">*</span></label>
                  <input id="mail_port" type="text" class="form-control" name="mail_port" maxlength="20" autocomplete="mail_port">
                </div>

                <div class="form-group col-md-4">
                  <label for="mail_encryption">Mail Encryption <span class="required">*</span></label>
                  <input id="mail_encryption" type="text" class="form-control" name="mail_encryption" maxlength="20" autocomplete="mail_encryption">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-12">
                  <label for="mail_host">Mail Host <span class="required">*</span></label>
                  <input id="mail_host" type="text" class="form-control" name="mail_host" maxlength="150" autocomplete="mail_host">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="mail_from_address">Mail From Address <span class="required">*</span></label>
                  <input id="mail_from_address" type="text" class="form-control" name="mail_from_address" maxlength="255" autocomplete="mail_from_address">
                </div>
                
                <div class="form-group col-md-6">
                  <label for="mail_from_name">Mail From Name <span class="required">*</span></label>
                  <input id="mail_from_name" type="text" class="form-control" name="mail_from_name" maxlength="150" autocomplete="mail_from_name">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="mail_username">Mail Username <span class="required">*</span></label>
                  <input id="mail_username" type="text" class="form-control" name="mail_username" maxlength="100" autocomplete="mail_username">
                </div>
                
                <div class="form-group col-md-6">
                  <label for="mail_password">Mail Password <span class="required">*</span></label>
                  <input id="mail_password" type="text" class="form-control" name="mail_password" maxlength="255" autocomplete="mail_password">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-12">
                  <label for="api_key">Api Key</label>
                  <input id="api_key" type="text" class="form-control" name="api_key" maxlength="255" autocomplete="api_key">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="customer_number">Customer Number</label>
                  <input id="customer_number" type="text" class="form-control" name="customer_number" maxlength="100" autocomplete="customer_number">
                </div>
                
                <div class="form-group col-md-6">
                  <label for="password">Password</label>
                  <input id="password" type="password" class="form-control" name="password" maxlength="255">
                </div>
              </div>

              <button type="button" class="btn btn-primary btn-lg btn-block createShop"><i class="fas fa-plus"></i> Create Shop</button>
            </div>

          </div>
        </div>
      </div>

    </main>
@endsection
