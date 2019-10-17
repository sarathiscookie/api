@extends('admin.layouts.app')

@section('title', 'Module List')

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-list-alt"></i> Module List</li>
        </ol>
      </nav>

      <div class="card border-primary">
        <div class="card-header bg-primary">
          Module List
        </div>

        <div class="card-body">
          <div class="text-right">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createModuleModal"><i class="fas fa-plus"></i> Create Module</button>
            <hr>
          </div>
          
          <div class="table-responsive">

            <div class="responseModuleMessage"></div>

            <table id="module_list" class="table table-bordered table-hover display" style="width:100%">
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th>Module Name</th>
                  <th>Active</th>
                  <th>Actions</th>
                </tr>
              </thead>

              <tbody></tbody>

              <tfoot>
                <td></td>
                <th><input type="text" id="1"  class="form-control input-sm search-input" placeholder="Search Module"></th>
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
  
      <!-- Create module modal -->
      <div class="modal fade" id="createModuleModal" tabindex="-1" role="dialog" aria-labelledby="createModuleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="createModuleModalLabel">Create Module</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">

              <div class="moduleValidationAlert"></div>

              <div class="form-row">
                <div class="form-group col-md-12">
                  <label for="module">Module Name <span class="required">*</span></label>
                  <input id="module" type="text" class="form-control" name="module" autocomplete="module" maxlength="150" autofocus>
                </div>
              </div>

              <button type="button" class="btn btn-primary btn-lg btn-block createModule"><i class="fas fa-plus"></i> Create Module</button>
            </div>

          </div>
        </div>
      </div>

    </main>
@endsection
