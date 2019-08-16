<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} - @yield('title')</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Default laravel Styles -->
    <link href="{{ asset('css/plugins.css') }}" rel="stylesheet">

    <!-- Each page Styles -->
    <link href="{{ asset('css/all.css') }}" rel="stylesheet">

    @yield('css')
</head>
<body>
    <div id="app" class="site-wrapper">
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark navbar-right">
        <a class="" href="#">{{ config('app.name') }}</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown active">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Welcome {{ Auth::user()->name }} <span class="caret"></span>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="/admin/dashboard"><i class="fas fa-home"></i> Dashboard</a>
                        <a class="dropdown-item" href="/admin/dashboard/company/list"><i class="fas fa-list-alt"></i> Company List</a>
                        <a class="dropdown-item" href="/admin/dashboard/manager/list"><i class="far fa-address-book"></i> Manager List</a>
                        <a class="dropdown-item" href="/admin/dashboard/shop/list"><i class="fas fa-store"></i> Shop List</a>
                        <a class="dropdown-item" href="/admin/dashboard/user/list"><i class="fas fa-users"></i> User List</a>
                        <a class="dropdown-item" href="/admin/dashboard/key/list"><i class="fas fa-key"></i> Key List</a>
                        <a class="dropdown-item" href="/admin/dashboard/supplier/list"><i class="fas fa-user-tie"></i> Supplier List</a>
                        <a class="dropdown-item" href="/admin/dashboard/product/list"><i class="fas fa-list-ul"></i> Product List</a>
                        
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" tabindex="-1">Help</a>
                </li>
            </ul>

        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block bg-light sidebar">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="/admin/dashboard">
                                <i class="fas fa-home"></i>
                                Dashboard <span class="sr-only">(current)</span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/dashboard/company/list">
                                <i class="fas fa-list-alt"></i>
                                Company List
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="/admin/dashboard/manager/list">
                                <i class="far fa-address-book"></i>
                                Manager List
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="/admin/dashboard/shop/list">
                                <i class="fas fa-store"></i>
                                Shop List
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="/admin/dashboard/user/list">
                                <i class="fas fa-users"></i> 
                                User List
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="/admin/dashboard/key/list">
                                <i class="fas fa-key"></i>
                                Key List
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="/admin/dashboard/supplier/list">
                                <i class="fas fa-user-tie"></i>
                                Supplier List
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="/admin/dashboard/product/list">
                                <i class="fas fa-list-ul"></i>
                                Product List
                            </a>
                        </li>

                    </ul>
                </div>
            </nav>

            @yield('content')

        </div>
    </div>
</div>

<!-- Default laravel Scripts -->
<script src="{{ asset('js/app.js') }}"></script>

<!-- Plugins Scripts -->
<script src="{{ asset('js/plugins.js') }}"></script>

<!-- Each page Scripts -->
<script src="{{ asset('js/all.js') }}"></script>

@stack('scripts')

</body>
</html>
