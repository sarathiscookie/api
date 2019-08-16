@extends('admin.layouts.app')

@section('title', 'Product List')

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-list-ul"></i> Product List</li>
        </ol>
      </nav>

      <div class="card border-primary">
        <div class="card-header bg-primary">
          Product List
        </div>

        <div class="card-body">
          <div class="text-right">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#productModal"><i class="fab fa-get-pocket"></i> Get Products</button>
            <hr>
          </div>
          
          <div class="table-responsive">

            <div class="responseProductMessage"></div>

            <table id="product_list" class="table table-bordered table-hover display" style="width:100%">
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
                <th><input type="text" id="1"  class="form-control input-sm search-input" placeholder="Search Product or Company"></th>
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
  
      <!-- Get products modal -->
      <div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="productModalLabel">Get Products</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">

              <div class="productValidationAlert"></div>

              <div class="form-row">
                <div class="form-group col-md-12">
                  <label for="product_company">Company</label>
                  <select id="product_company" class="form-control" name="product_company">
                    <option value="">Choose Company</option>
                    @isset($companies)
                    @foreach( $companies as $company )
                    <option value="{{ $company->id }}">{{ $company->company }}</option>
                    @endforeach
                    @endisset
                  </select>
                </div>
              </div>  

              <button type="button" class="btn btn-primary btn-lg btn-block getProducts"><i class="fab fa-get-pocket"></i> Get Products</button>
            </div>

          </div>
        </div>
      </div>

    </main>
@endsection
