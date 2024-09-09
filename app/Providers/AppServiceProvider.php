<?php

namespace App\Providers;

use App\Http\Controllers\GitController;
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
            $gitController->autoRunStart();

        //     session()->put('auto_run_started', true);
        // }
    }
}
