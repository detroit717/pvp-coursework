<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateDriver
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('driver_id')) {
            return redirect('/login');
        }
        return $next($request);
    }
}
