@extends('admin.layouts.app')

@section('title', 'Product List')

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
          <li class="breadcrumb-item active"><a href="/admin/dashboard/product/get"><i class="fas fa-list-ul"></i> Get Products</a></li>
          <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-list-ul"></i> Product List</li>
        </ol>
      </nav>

      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Filter</h5>
              <div class="row">
                <div class="form-group col-md-12">
                  <label for="shopCategoriesSelect">Category:</label>
                  <select class="form-control" id="shopCategoriesSelect" name="shopCategoriesSelect">
                    <option class="noCategoriesFound" value="">Choose Category</option>
                  </select>
                </div>

              </div>

              <div class="row">

                <div class="form-group col-md-3">
                  <label for="visible">Visible:</label>
                  <div>
                    <a href="" class="btn btn-secondary btn-sm visibleActive"><i class="fas fa-check-circle"></i></a>
                    <a href="" class="btn btn-secondary btn-sm visibleDisable"><i class="fas fa-times-circle"></i></a>
                    <a href="" class="btn btn-secondary btn-sm visibleAll">All</a>
                  </div>
                </div>

                <div class="form-group col-md-3">
                  <label for="available">Available:</label>
                  <div>
                    <a href="" class="btn btn-secondary btn-sm availableActive"><i class="fas fa-check-circle"></i></a>
                    <a href="" class="btn btn-secondary btn-sm availableDisable"><i class="fas fa-times-circle"></i></a>
                    <a href="" class="btn btn-secondary btn-sm availableAll">All</a>
                  </div>
                </div>

                <div class="form-group col-md-3">
                  <label for="exampleFormControlSelect1">Active:</label>
                  <div>
                    <a href="#" class="btn btn-secondary btn-sm"><i class="fas fa-check-circle"></i></a>
                    <a href="#" class="btn btn-secondary btn-sm"><i class="fas fa-times-circle"></i></a>
                  </div>
                </div>

                <div class="form-group col-md-3">
                  <label for="exampleFormControlSelect1">Config:</label>
                  <div>
                    <a href="#" class="btn btn-secondary btn-sm"><i class="fas fa-check-circle"></i></a>
                    <a href="#" class="btn btn-secondary btn-sm"><i class="fas fa-times-circle"></i></a>
                  </div>
                </div>

              </div>
              
              
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12 mt-3">
          <div class="card border-primary">
            <div class="card-header bg-primary">
              Product List
            </div>

            <div class="card-body">
             <div class="table-responsive">

              <div class="responseProductListMessage"></div>
              <input type="hidden" name="productListShopId" value="{{ $shopId }}" class="productListShopIdClass">
              <input type="hidden" name="productListCompanyId" value="{{ $companyId }}" class="productListCompanyIdClass">

              <table id="product_list" class="table table-bordered table-hover display" style="width:100%">
                <thead class="thead-light">
                  <tr>
                    <th>#</th>
                    <th>Product Name</th>
                    <th>Active</th>
                    <th>Module Settings</th>
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
      </div>
    </div>

    </main>
@endsection
