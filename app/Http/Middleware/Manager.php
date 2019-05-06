<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Manager
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
        if (Auth::check() && Auth::user()->role == 'manager') {
            return $next($request);
            //return redirect('/manager/dashboard');
        }
        // elseif (Auth::check() && Auth::user()->role == 'employee') {
        //     return redirect('/employee/dashboard');
        // }
        // else {
        //     return redirect('/admin/dashboard');
        // }
    }
}
