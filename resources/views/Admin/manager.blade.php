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
                  <th>Role</th>
                  <th>Created</th>
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
                  <td>{{ $user->role }}</td>
                  <td>{{ date('d.m.y', strtotime($user->created_at)) }}</td>
                  <td>{{ $user->active }} <input type="checkbox" class="toggle-one" checked data-toggle="toggle" data-size="mini" data-onstyle="success" data-offstyle="danger"></td>
                  <td><span data-feather="edit"></span>  <span data-feather="trash-2"></span></td>
                </tr>
                @endforeach
                @endisset

                @empty($users)
                <p>No users</p>
                @endempty
                
              </tbody>

              {{ $users->links() }}

            </table>
          </div>
        </div>
      </div>

    </main>
@endsection
