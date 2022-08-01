<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OwnerAndStaffMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()->role != 'owner' && auth()->user()->role != 'staff') {
            return responseJson(false, 'You are not authorized to access this resource', null, 401);
        }
        return $next($request);
    }
}
