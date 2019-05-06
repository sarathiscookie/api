<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */

    protected $redirectTo;

    public function redirectTo() 
    {
        if (Auth::check() && Auth::user()->role == 'employee') {
            $this->redirectTo = '/employee/dashboard';
            return $this->redirectTo;
        }
        elseif (Auth::check() && Auth::user()->role == 'manager') {
            $this->redirectTo = '/manager/dashboard';
            return $this->redirectTo;
        }
        elseif (Auth::check() && Auth::user()->role == 'admin') {
            $this->redirectTo = '/admin/dashboard';
            return $this->redirectTo;
        }
        else {
            $this->redirectTo = '/';
            return $this->redirectTo;
        }
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
