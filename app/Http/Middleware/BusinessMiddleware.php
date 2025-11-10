<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'business') {
            return $next($request);
        }
        return response()->json([
            'error' => 'You do not have access to this section.'
            
        ],404);
        // return redirect()->route('login')->with('error', 'You do not have access to this section.');
    }
}
