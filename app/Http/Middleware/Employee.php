<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Employee
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role == 'employee') {
            return $next($request);
            //return redirect('/employee/dashboard');
        }
        // elseif (Auth::check() && Auth::user()->role == 'manager') {
        //     return redirect('/manager/dashboard');
        // }
        // else {
        //     return redirect('/admin/dashboard');
        // }
    }
}
