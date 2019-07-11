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

            <table id="{{-- shop_list --}}" class="table table-bordered table-hover display" style="width:100%">
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
  
      <!-- Create shop modal -->
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
                  <label for="shop_name">Shop <span class="required">*</span></label>

                  <select class="form-control" id="shop_name" name="shop_name">
                    <option>Choose shop name</option>
                    @isset($shopNames)
                    @foreach($shopNames as $shopName) 
                    <option value="{{ $shopName->id }}">{{ $shopName->name }}</option>
                    @endforeach
                    @endisset
                  </select>

                </div>
                <div class="form-group col-md-6">
                  <label for="shop_company">Company <span class="required">*</span></label>
                  <select id="shop_company" class="form-control" name="shop_company">
                    <option value="">Choose...</option>
                    @isset($companies)
                      @foreach( $companies as $company )
                          <option value="{{ $company->id }}">{{ $company->company }}</option>
                      @endforeach
                    @endisset
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="shop_mail_driver">Mail Driver <span class="required">*</span></label>
                  <input id="shop_mail_driver" type="text" class="form-control" name="shop_mail_driver" maxlength="150">
                </div>

                <div class="form-group col-md-4">
                  <label for="shop_mail_port">Mail Port <span class="required">*</span></label>
                  <input id="shop_mail_port" type="text" class="form-control" name="shop_mail_port" maxlength="20">
                </div>

                <div class="form-group col-md-4">
                  <label for="shop_mail_encryption">Mail Encryption <span class="required">*</span></label>
                  <input id="shop_mail_encryption" type="text" class="form-control" name="shop_mail_encryption" maxlength="20">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-12">
                  <label for="shop_mail_host">Mail Host <span class="required">*</span></label>
                  <input id="shop_mail_host" type="text" class="form-control" name="shop_mail_host" maxlength="150">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="shop_mail_from_address">Mail From Address <span class="required">*</span></label>
                  <input id="shop_mail_from_address" type="text" class="form-control" name="shop_mail_from_address" maxlength="255">
                </div>
                
                <div class="form-group col-md-6">
                  <label for="shop_mail_from_name">Mail From Name <span class="required">*</span></label>
                  <input id="shop_mail_from_name" type="text" class="form-control" name="shop_mail_from_name" maxlength="150">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="shop_mail_username">Mail Username <span class="required">*</span></label>
                  <input id="shop_mail_username" type="text" class="form-control" name="shop_mail_username" maxlength="100">
                </div>
                
                <div class="form-group col-md-6">
                  <label for="shop_mail_password">Mail Password <span class="required">*</span></label>
                  <input id="shop_mail_password" type="password" class="form-control" name="shop_mail_password" maxlength="255">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-12">
                  <label for="shop_api_key">Api Key</label>
                  <input id="shop_api_key" type="text" class="form-control" name="shop_api_key" maxlength="255">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="shop_customer_number">Customer Number</label>
                  <input id="shop_customer_number" type="text" class="form-control" name="shop_customer_number" maxlength="100">
                </div>
                
                <div class="form-group col-md-6">
                  <label for="shop_password">Password</label>
                  <input id="shop_password" type="password" class="form-control" name="shop_password" maxlength="255">
                </div>
              </div>

              <button type="button" class="btn btn-primary btn-lg btn-block createShop"><i class="fas fa-plus"></i> Create Shop</button>
            </div>

          </div>
        </div>
      </div>

    </main>
@endsection
