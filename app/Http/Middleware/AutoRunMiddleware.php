<?php

namespace App\Http\Middleware;

use App\Http\Controllers\GitController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutoRunMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle($request, Closure $next)
    {
        // if (!session()->has('auto_run_started')) {
            $gitController = new GitController();
            $gitController->autoRunStart();
        //     session()->put('auto_run_started', true);
        // }

        return $next($request);
    }
}
