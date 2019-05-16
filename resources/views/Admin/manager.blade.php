@extends('admin.layouts.app')

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-list"></i> Manager List</li>
        </ol>
      </nav>

      <div class="card border-primary">
        <div class="card-header bg-primary">
          Manager List
        </div>

        <div class="card-body">
          <div class="text-right">
            <a href="/admin/dashboard/manager/create" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Manager</a>
            <hr>
          </div>
          
          <div class="table-responsive">
            @if (session()->has('successStoreManager'))
            <div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> 
              {{ session()->get('successStoreManager') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>  
            @endif

            @if (session()->has('successUpdateManager'))
            <div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> 
              {{ session()->get('successUpdateManager') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>  
            @endif

            <div class="responseMessage"></div>

            <table id="datatable_list" class="table table-bordered table-hover display" style="width:100%">
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

            <!-- Export buttons are append here -->
            <div id="buttons"></div>

          </div>
        </div>

      </div>

    </main>
@endsection
