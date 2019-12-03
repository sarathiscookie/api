@extends('admin.layouts.app')

@section('title', 'Order List')

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-list-ul"></i> Order List</li>
        </ol>
      </nav>

      <div class="card border-primary">
        <div class="card-header bg-primary">
          Order List
        </div>

        <div class="card-body">

          <div class="form-row">
            <div class="form-group col-md-4">
              <select id="orderCompany" class="form-control">
                <option value="">Choose company</option>
                @isset($companies)
                @foreach( $companies as $company )
                <option value="{{ $company->id }}">{{ $company->company }}</option>
                @endforeach
                @endisset
              </select>
            </div>

            <div class="form-group col-md-4">
              <input type="text" class="form-control" id="orderListDateRange" name="orderListDateRange" placeholder="daterange">
            </div>

            <div class="form-group col-md-4">
              <button type="submit" class="btn btn-primary" id="generateOrders"> Generate Orders</button>
            </div>

            <div class="alertMsg"></div>
          </div>
          <hr>

          <div class="text-right">
            <button type="button" class="btn btn-primary"><i class="fas fa-download"></i> Download All Invoices</button>
            <hr>
          </div>
          
          <div class="table-responsive">

            <div class="orderResponseKeyMessage"></div>

            <table id="" class="table table-bordered table-hover display" style="width:100%">
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th>Order Details</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>

              <tbody></tbody>

              <tfoot>
                <td></td>
                <th><input type="text" id="1"  class="form-control input-sm search-input" placeholder="Order No"></th>
                <th>
                  <select class="form-control input-sm search-input" id="2">
                    <option value="">All</option>
                    <option value="yes">Active</option>
                    <option value="no">Not Active</option>
                  </select>
                </th>
                <td></td>
              </tfoot>

            </table>

          </div>
        </div>
      </div>

    </main>
@endsection
