@extends('admin.layouts.app')

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page">Manager List</li>
        </ol>
      </nav>

      <div class="card text-white border-primary">
        <div class="card-header bg-primary">
          Manager List
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Created on</th>
                  <th>Status</th>
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
                  <td class="toggle-btn active" >{{ $user->active }} <input type="checkbox" checked class="cb-value"> <span class="round-btn"></span></td>
                  <td><span data-feather="edit"></span>  <span data-feather="trash-2"></span></td>
                  {{-- https://codepen.io/nikhil8krishnan/pen/eNVZgB --}}
                </tr>
                @endforeach
                @endisset

                @empty($users)
                <p>No users</p>
                @endempty
                
              </tbody>
            </table>
            {{ $users->links() }}
          </div>
        </div>
      </div>

    </main>
@endsection
