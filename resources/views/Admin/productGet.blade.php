@extends('admin.layouts.app')

@section('title', 'Get Products')

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-list-ul"></i> Get Products</li>
        </ol>
      </nav>

      <div class="card border-primary">
        <div class="card-header bg-primary">
          Get Products
        </div>

        <div class="card-body">
          <div class="col-md-12">

            <div class="form-row">
              <div class="form-group col-md-12">
                <label for="product_shop">Shop</label>
                <select id="product_shop" class="form-control product_shop" name="product_shop">
                  <option value="">Choose Shop</option>
                  @isset($shopNames)
                  @foreach( $shopNames as $shopName )
                  <option value="{{ $shopName->id }}">{{ $shopName->name }}</option>
                  @endforeach
                  @endisset
                </select>
              </div>
            </div> 

            <div class="form-row">
              <div class="form-group col-md-12">
                <label for="product_company">Company</label>
                <select id="product_company" class="form-control" name="product_company">
                  <option value="">Choose Company</option>
                  @isset($companies)
                  @foreach( $companies as $company )
                  <option value="{{ $company->id }}">{{ $company->company }}</option>
                  @endforeach
                  @endisset
                </select>
              </div>
            </div>

            <a href="" class="btn btn-primary btn-lg btn-block getProducts"><i class="fab fa-get-pocket"></i> Get Products</a>
          </div>

        </div>
      </div>

    </main>
@endsection
