<?php

namespace App\Providers;

use App\Http\Controllers\GitController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // if (!session()->has('auto_run_started')) {
        $gitController = new GitController();
        // $repositories = $gitController->getRepositories('C:\Users\Murilo Carazato\Documents\Flutter Projects\assis-ofertas');
        $repositories = $gitController->getRepositories('C:\Users\Murilo Carazato\Documents\Flutter Projects');

        foreach ($repositories as $repo) {
            Cache::forget("repo_started_{$repo['path']}");
        }

        $gitController->autoRunStart();

        //     session()->put('auto_run_started', true);
        // }
    }
}
