@extends('admin.layouts.app')

@section('title', 'Key List')

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-key"></i> Key List</li>
        </ol>
      </nav>

      <div class="card border-primary">
        <div class="card-header bg-primary">
          Key List
        </div>

        <div class="card-body">
          <div class="text-right">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createKeyModal"><i class="fas fa-key"></i> Create Key</button>
            <hr>
          </div>
          
          <div class="table-responsive">

            <div class="responseKeyMessage"></div>

            <table id="key_list" class="table table-bordered table-hover display" style="width:100%">
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th>Key Details</th>
                  <th>Active</th>
                  <th>Actions</th>
                </tr>
              </thead>

              <tbody></tbody>

              <tfoot>
                <td></td>
                <th><input type="text" id="1"  class="form-control input-sm search-input" placeholder="Search key name"></th>
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
            <div id="buttons"></div>

          </div>
        </div>
      </div>
  
      <!-- Create key modal -->
      <div class="modal fade" id="createKeyModal" tabindex="-1" role="dialog" aria-labelledby="createKeyModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="createKeyModalLabel">Create Key</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">

              <div class="keyValidationAlert"></div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="key_name">Key Name <span class="required">*</span></label>
                  <input type="text" name="key_name" id="key_name" class="form-control" name="category" maxlength="100">
                </div>

                <div class="form-group col-md-6">
                  <label for="key_type">Key Type <span class="required">*</span></label>
                  <select id="key_type" class="form-control" name="key_type">
                    <option value="">Choose Type</option>
                    @isset($keyTypes)
                    @foreach ($keyTypes as $keyValue => $keyType)
                      <option value="{{$keyValue}}">{{$keyType}}</option>
                    @endforeach
                    @endisset
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="company">Company <span class="required">*</span></label>
                  <select id="company" class="form-control" name="company">
                    <option value="">Choose Company</option>
                    @isset($companies)
                    @foreach ($companies as $company)
                      <option value="{{$company->id}}">{{$company->company}}</option>
                    @endforeach
                    @endisset
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-12" id="divShop">
                  <div id="noShopsAlert"></div>
                  <div style="display:none;" id="shopSelectBoxDiv">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="checkAllShops">
                      <label class="form-check-label" for="gridCheck">
                        Click check box to select all shops. Each shops can select separately by clicking on select box.
                      </label>
                    </div>
                    <select id="shop" class="form-control" name="shop[]" multiple="multiple">
                      <option id="optionChoose" value="" disabled="disabled">Choose Shop</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-12">
                  <label for="key">Key <i class="far fa-question-circle" data-toggle="tooltip" data-placement="right" title="You can separated keys with commas, space and new line. But dont mix with these."></i><span class="required">*</span></label>
                  <textarea class="form-control" name="keys" id="keys" rows="3"></textarea>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="activation_number">Activation Number <span class="required">*</span></label>
                  <input type="number" id="activation_number" class="form-control" name="activation_number" maxlength="10">
                </div>
              </div>

              <button type="button" class="btn btn-primary btn-lg btn-block createKey"><i class="fas fa-key"></i> Create Key</button>
            </div>

          </div>
        </div>
      </div>

    </main>
@endsection
