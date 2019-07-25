@extends('admin.layouts.app')

@section('title', 'Key Instruction List')

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-key"></i> Key Instruction List</li>
        </ol>
      </nav>

      <div class="card border-primary">
        <div class="card-header bg-primary">
          Key Instruction List
        </div>

        <div class="card-body">
          <div class="text-right">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createKeyInstructionModal"><i class="fas fa-plus"></i> Create Key Instruction</button>
            <hr>
          </div>
          
          <div class="table-responsive">

            <div class="responseKeyInstructionMessage"></div>

            <table id="key_instruction_list" class="table table-bordered table-hover display" style="width:100%">
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
                <th><input type="text" id="1"  class="form-control input-sm search-input" placeholder="Key name or Company or Shops"></th>
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
  
      <!-- Create key instruction modal -->
      <div class="modal fade" id="createKeyInstructionModal" tabindex="-1" role="dialog" aria-labelledby="createKeyInstructionLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="createKeyInstructionLabel">Create Key Instruction</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">

              <div class="keyInstructionValidationAlert"></div>

              <div class="form-group col-md-6">
                <label for="key_instruction_language">Company <span class="required">*</span></label>
                <select id="key_instruction_language" class="form-control" name="key_instruction_language">
                  <option value="">Choose Language</option>
                  @isset($countries)
                  @foreach ($countries as $country)
                  <option value="{{$country->id}}">{{$country->code}}</option>
                  @endforeach
                  @endisset
                </select>
              </div>

              <div class="form-group col-md-12">
                <label for="key_instruction_url">Key Instruction Url <span class="required">*</span></label>
                <input type="file" id="key_instruction_url" class="form-control-file" name="key_instruction_url">
              </div>

              <button type="button" class="btn btn-primary btn-lg btn-block createKeyInstruction"><i class="fas fa-plus"></i> Create Instruction </button>
            </div>

          </div>
        </div>
      </div>

    </main>
@endsection
