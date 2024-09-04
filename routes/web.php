<?php

use App\Http\Controllers\GitController;
use Illuminate\Support\Facades\Route;

//auto run server
//mostrar consoles dos servidores

//ver servers rodando em segundo plano:
//tasklist /FI "IMAGENAME eq php.exe"
//dps excluir eles:
//taskkill /F /IM php.exe

Route::get('/', [GitController::class, 'index']);//->middleware('auth')
Route::post('/git/pull', [GitController::class, 'pull'])->name('git.pull');
Route::post('/serve', [GitController::class, 'serve'])->name('git.serve');
Route::post('/clear-messages', [GitController::class, 'clearMessages'])->name('git.clearMessages');