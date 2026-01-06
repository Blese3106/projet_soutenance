<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()) {
            return redirect('/login');
        }

        if ($request->user()->role !== 'admin') {
            abort(403, 'Vous ne pouvez pas accéder à cette zone.');
        }

        return $next($request);
    }
}
