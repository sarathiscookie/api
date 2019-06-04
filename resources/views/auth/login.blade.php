@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('login') }}" class="form-signin">
  @csrf

  <img class="mb-4" src="https://getbootstrap.com/docs/4.0/assets/brand/bootstrap-solid.svg" alt="" width="72" height="72">
  <h1 class="h3 mb-3 font-weight-normal">{{ __('signin.Signin Heading') }}</h1>

  <label for="login" class="sr-only">{{ __('signin.E-Mail Address') }}</label>
  <input id="login" type="text" class="form-control {{ $errors->has('username') || $errors->has('email') ? ' is-invalid' : '' }}" name="login" value="{{ old('username') ?: old('email') }}" placeholder="{{ __('signin.E-Mail Address') }}" autocomplete="login" autofocus>
  
  @if ($errors->has('username') || $errors->has('email'))
  <span class="invalid-feedback" role="alert">
    <strong>{{ $errors->first('username') ?: $errors->first('email') }}</strong>
  </span>
  @endif

  <label for="password" class="sr-only">{{ __('signin.Password') }}</label>
  <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="{{ __('signin.Password') }}" autocomplete="current-password">
  @error('password')
  <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
  @enderror

  <div class="checkbox mb-3">
    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
    <label class="form-check-label" for="remember">{{ __('signin.Remember Me') }}</label>
  </div>

  <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
  <p class="mt-5 mb-3 text-muted">&copy; @php echo date('Y'); @endphp</p>

</form>
@endsection