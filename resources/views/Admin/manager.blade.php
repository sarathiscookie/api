@extends('admin.layouts.app')

@section('title', 'Manager List')

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page"><i class="far fa-address-book"></i> Manager List</li>
        </ol>
      </nav>

      <div class="card border-primary">
        <div class="card-header bg-primary">
          Manager List
        </div>

        <div class="card-body">
          <div class="text-right">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createManagerModal"><i class="fas fa-user-plus"></i> Create Manager</button>
            <hr>
          </div>
          
          <div class="table-responsive">

            <div class="responseMessage"></div>

            <table id="manager_list" class="table table-bordered table-hover display" style="width:100%">
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Active</th>
                  <th>Actions</th>
                </tr>
              </thead>

              <tbody></tbody>

              <tfoot>
                <td></td>
                <th><input type="text" id="1"  class="form-control input-sm search-input" placeholder="Search Name"></th>
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

          </div>
        </div>
      </div>
  
      <!-- Create manager modal -->
      <div class="modal fade" id="createManagerModal" tabindex="-1" role="dialog" aria-labelledby="createManagerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="createManagerModalLabel">Create Manager</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">

              <div class="managerValidationAlert"></div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="name">Name <span class="required">*</span></label>
                  <input id="name" type="text" class="form-control" name="name" autocomplete="name" maxlength="255" autofocus>
                </div>

                <div class="form-group col-md-6">
                  <label for="email">Email <span class="required">*</span></label>
                  <input id="email" type="email" class="form-control" name="email" maxlength="255" autocomplete="email">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="username">Username <span class="required">*</span></label>
                  <input id="username" type="text" class="form-control" name="username" maxlength="255">
                </div>

                <div class="form-group col-md-4">
                  <label for="password">Password <span class="required">*</span></label>
                  <input id="password" type="password" class="form-control" name="password" maxlength="255">
                </div>

                <div class="form-group col-md-4">
                  <label for="password_confirmation">Confirm Password <span class="required">*</span></label>
                  <input id="password_confirmation" type="password" class="form-control" name="password_confirmation">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="manager_company">Company <span class="required">*</span></label>
                  <select id="manager_company" class="form-control" name="manager_company[]" multiple="multiple">
                    @isset($companies)
                      @foreach( $companies as $company )
                          <option value="{{ $company->id }}">{{ $company->company }}</option>
                      @endforeach
                    @endisset
                  </select>
                </div>

                <div class="form-group col-md-6">
                  <label for="phone">Phone <span class="required">*</span></label>
                  <input id="phone" type="text" class="form-control" name="phone" maxlength="20" autocomplete="phone">
                </div>
                
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="country">Country <span class="required">*</span></label>
                  <select id="country" class="form-control" name="country">
                    <option value="">Choose...</option>
                    @foreach ($countries as $country)
                      <option value="{{$country->id}}">{{$country->name}}</option>
                    @endforeach
                  </select>
                </div>

                <div class="form-group col-md-6">
                  <label for="city">City <span class="required">*</span></label>
                  <input id="city" type="text" class="form-control" name="city" maxlength="255" autocomplete="city">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="street">Street <span class="required">*</span></label>
                  <input id="street" type="text" class="form-control" name="street" maxlength="255" autocomplete="street">
                </div>

                <div class="form-group col-md-6">
                  <label for="zip">Zip <span class="required">*</span></label>
                  <input id="zip" type="text" class="form-control" name="zip" maxlength="20" autocomplete="zip">
                </div>
              </div>

              <button type="button" class="btn btn-primary btn-lg btn-block createManager"><i class="fas fa-user-plus"></i> Create Manager</button>
            </div>

          </div>
        </div>
      </div>

    </main>
@endsection
