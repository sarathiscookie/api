@extends('admin.layouts.app')

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
    	<nav aria-label="breadcrumb">
    		<ol class="breadcrumb">
    			<li class="breadcrumb-item"><a href="/admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
    			<li class="breadcrumb-item"><a href="/admin/dashboard/manager/list"><i class="fas fa-list"></i> Manager List</a></li>
    			<li class="breadcrumb-item active" aria-current="page"><i class="fas fa-user-edit"></i> Edit Manager</li>
    		</ol>
    	</nav>

    	<div class="card border-primary">
    		<div class="card-header bg-primary">
    			Edit Manager
    		</div>

    		<div class="card-body">
                @isset($user)
                @if (session()->has('failureUpdateManager'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> 
                    {{ session()->get('failureUpdateManager') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>  
                @endif

                <div class="text-right">
                    <a href="" type="button" class="btn btn-danger btn-sm deleteEvent" data-id="'.$managerList->id.'"><i class="fas fa-trash-alt"></i> Delete</a>
                    <hr>
                </div>

                <form method="POST" action="{{ route('admin.dashboard.manager.update') }}">
                    @csrf

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="name">Name</label>

                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" maxlength="255" autofocus>

                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="phone">Phone</label>

                            <input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="20" autocomplete="phone">

                            @error('phone')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="company">Company</label>
                            <select id="company" class="form-control @error('company') is-invalid @enderror" name="company">
                                <option value="">Choose Company</option>
                                <option value="tcs" @if(old('company', $user->company) === 'tcs') selected @endif>TCS</option>
                            </select>

                            @error('company')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="street">Street</label>

                            <input id="street" type="text" class="form-control @error('street') is-invalid @enderror" name="street" value="{{ old('street', $user->street) }}" maxlength="255" autocomplete="street">

                            @error('street')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="city">City</label>

                            <input id="city" type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city', $user->city) }}" maxlength="255" autocomplete="city">

                            @error('city')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-4">
                            <label for="country">Country</label>

                            <select id="country" class="form-control @error('country') is-invalid @enderror" name="country">
                                <option value="">Choose Country</option>
                                <option value="de" @if(old('country', $user->country) === 'de') selected @endif>Germany</option>
                            </select>

                            @error('country')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-2">
                            <label for="zip">Zip</label>

                            <input id="zip" type="text" class="form-control @error('zip') is-invalid @enderror" name="zip" value="{{ old('zip', $user->postal) }}" maxlength="20" autocomplete="zip">

                            @error('zip')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="fas fa-user-edit"></i> Update Manager</button>
                </form>
                @endisset
    			
    		</div>
    	</div>
    </main>
@endsection
