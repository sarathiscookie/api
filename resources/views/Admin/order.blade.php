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
              <input type="text" class="form-control" id="orderListDateRange" name="orderListDateRange" placeholder="daterange" autocomplete="off">
            </div>

            <div class="form-group col-md-4">
              <button type="button" class="btn btn-primary" id="generateOrders"> Fetching Orders</button>
            </div>

            <div class="alertMsg"></div>
          </div>

          <hr>

          <div class="text-right downloadAllInvoiceDiv">
            <form action="/admin/dashboard/order/list/download/all/invoices" method="POST">
              @csrf
              <input type="checkbox" class="form-check-input allFilesCheckedInput" name="allFilesChecked" id="allFilesChecked">
              <label class="form-check-label allFilesCheckedLabel" for="allFilesChecked">Click here to select all files to download</label>
              <button class="btn btn-primary"><i class="fas fa-download"></i> Download Invoices</button>
              <input type="hidden" name="inputOrderCompanyId" id="inputOrderCompanyId" value=""/>
              <input type="hidden" name="inputOrderNoArr" id="inputOrderNoArr" value=""/>
              <input type="hidden" name="inputOrderDateRange" id="inputOrderDateRange" value=""/>
              <input type="hidden" name="orderListPages" id="orderListPages" value=""/>
              <input type="hidden" name="orderListPerPage" id="orderListPerPage" value=""/>
              <input type="hidden" name="orderListTotal" id="orderListTotal" value=""/>
            </form>
            <hr>
          </div>

          <div class="table-responsive">

            <div class="orderResponseKeyMessage"></div>

            <table id="order_list" class="table table-bordered table-hover display" style="width:100%">
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th>Order Details</th>
                  <th>Status</th>
                  <th>Download</th>
                </tr>
              </thead>

              <tbody></tbody>

              <tfoot>
                <td></td>
                <th><input type="text" id="1"  class="form-control input-sm search-input" placeholder="Order No"></th>
                <th>
                  <select class="form-control input-sm search-input" id="2">
                    <option value="">All</option>
                    @isset($orderStatuses)
                    @foreach( $orderStatuses as $orderKey => $orderStatus )
                    <option value="{{ $orderKey }}">{{ $orderStatus }}</option>
                    @endforeach
                    @endisset
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
