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
        $gitController = new GitController();
        $gitController->autoRunStart();

        return $next($request);
    }
}
