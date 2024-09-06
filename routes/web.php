<?php

use App\Http\Controllers\GitController;
use App\Http\Middleware\AutoRunMiddleware;
use Illuminate\Support\Facades\Route;

//ver servers rodando em segundo plano:
//tasklist /FI "IMAGENAME eq php.exe"
//dps excluir eles:
//taskkill /F /IM php.exe

// Route::middleware([AutoRunMiddleware::class])->group(function () {
Route::get('/', [GitController::class, 'index']);
// });
Route::post('/git/pull', [GitController::class, 'pull'])->name('git.pull');
Route::post('/git/auto-run-switch', [GitController::class, 'toggleAutoRun'])->name('git.autoRunSwitch');
Route::post('/serve', [GitController::class, 'serve'])->name('git.serve');
Route::post('/clear-messages', [GitController::class, 'clearMessages'])->name('git.clearMessages');
