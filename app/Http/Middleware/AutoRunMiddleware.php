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
    // public function handle($request, Closure $next)
    // {
    //     $gitController = new GitController();
    //     $gitController->autoRunStart();

    //     return $next($request);
    // }

    // public function handle($request, Closure $next)
    // {
    //     if ($request->isMethod('GET')) {
    //         $gitController = new GitController();
    //         $gitController->autoRunStart();
    //     }

    //     return $next($request);
    // }

    public function handle($request, Closure $next)
    {
        // Verifica se a flag de execução do autorun não está setada na sessão
        if (!session()->has('auto_run_started')) {
            $gitController = new GitController();
            $gitController->autoRunStart();
            // Define a flag de execução na sessão para evitar execuções repetidas
            session()->put('auto_run_started', true);
        }

        return $next($request);
    }
}
