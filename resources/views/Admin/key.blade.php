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
                <th><input type="text" id="1"  class="form-control input-sm search-input" placeholder="Search Key"></th>
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
                  <label for="shop">Shop <span class="required">*</span></label>
                  <select id="shop" class="form-control" name="shop">
                    <option value="0">Choose Shop</option>
                    @isset($shopDetails)
                    @foreach ($shopDetails as $shopDetail)
                      <option value="{{$shopDetail->id}}">{{$shopDetail->shop}}</option>
                    @endforeach
                    @endisset
                  </select>
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
                <div class="form-group col-md-12">
                  <label for="key">Key <span class="required">*</span></label>
                  <input id="key" type="text" class="form-control" name="key" maxlength="255" autocomplete="key">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="category">Category <span class="required">*</span></label>
                  <input id="category" type="category" class="form-control" name="category" maxlength="150" autocomplete="category">
                </div>

                <div class="form-group col-md-6">
                  <label for="language">Key Instruction Language <span class="required">*</span></label>
                  <select id="language" class="form-control" name="language">
                    <option value="">Choose Language</option>
                    @isset($languages)
                    @foreach ($languages as $language)
                      <option value="{{$language->id}}">{{$language->code}}</option>
                    @endforeach
                    @endisset
                  </select>
                </div>
              </div>

              <div class="form-row ">
                <div class="form-group col-md-12">
                  <label for="instruction">Key Instructions <span class="required">*</span></label>
                  <textarea class="form-control" name="instruction" id="instruction" rows="3"></textarea>
                </div>
              </div>

              <button type="button" class="btn btn-primary btn-lg btn-block createKey"><i class="fas fa-key"></i> Create Key</button>
            </div>

          </div>
        </div>
      </div>

    </main>
@endsection
