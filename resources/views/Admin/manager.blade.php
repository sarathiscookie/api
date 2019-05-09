@extends('admin.layouts.app')

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page">Manager List</li>
        </ol>
      </nav>

      <div class="card border-primary">
        <div class="card-header bg-primary">
          Manager List
        </div>
        <div class="card-body">
          <div class="table-responsive">
            
            <div class="responseMessage"></div>

            <table id="datatable_list" class="table table-bordered table-hover display" style="width:100%">
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Created on</th>
                  <th>Active</th>
                  <th>Actions</th>
                </tr>
              </thead>

              <tbody></tbody>

              <tfoot>
                <td></td>
                <td></td>
                <th><input type="text" id="2"  class="form-control input-sm search-input" placeholder="Search Email"></th>
                <td></td>
                <td>
                  <select class="form-control input-sm search-input" id="4">
                    <option value="">All</option>
                    <option value="yes">Active</option>
                    <option value="freeze">Freeze</option>
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
