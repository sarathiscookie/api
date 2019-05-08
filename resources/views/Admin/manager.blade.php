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
            <table id="example" class="table table-bordered table-hover display" style="width:100%">
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

              <tbody>
                @isset($users)
                @foreach ($users as $user)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $user->name }}</td>
                  <td>{{ $user->email }}</td>
                  <td>{{ date('d.m.y', strtotime($user->created_at)) }}</td>
                  <td>{{ $user->active }}</td>
                  <td><span data-feather="edit"></span>  <span data-feather="trash-2"></span></td>
                  {{-- https://codepen.io/nikhil8krishnan/pen/eNVZgB --}}
                </tr>
                @endforeach
                @endisset
              <tbody></tbody>

                @empty($users)
                <p>No users</p>
                @endempty
                
              </tbody>

              <tfoot>
                <td></td>
                <td></td>
                <th><input type="text" id="2"  class="form-control input-sm search-input" placeholder="Search Email"></th>
                <td></td>
                <td>
                  <select class="form-control input-sm search-input" id="4">
                    <option value="">Choose</option>
                    <option value="yes">yes</option>
                    <option value="no">no</option>
                  </select>
                </td>
                <td></td>
              </tfoot>

            </table>
          </div>
        </div>
      </div>

    </main>
@endsection
