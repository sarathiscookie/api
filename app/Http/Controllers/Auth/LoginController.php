<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $credentials = $request->only($this->username(), 'password');
        $credentials['active'] = 'yes';
        return $credentials;
    }

    /**
     * If the redirect path needs custom generation logic you may define a redirectTo method 
     * instead of a redirectTo property.
     */
    protected function redirectTo() 
    {
        if (Auth::check() && Auth::user()->role === 'employee') {
            $this->redirectTo = '/employee/dashboard';
            return $this->redirectTo;
        }
        elseif (Auth::check() && Auth::user()->role === 'manager') {
            $this->redirectTo = '/manager/dashboard';
            return $this->redirectTo;
        }
        elseif (Auth::check() && Auth::user()->role === 'admin') {
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
