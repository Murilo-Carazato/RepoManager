<?php

use App\Http\Controllers\GitController;
use Illuminate\Support\Facades\Route;

//auto run server
//comandos mais usados no git

Route::get('/', [GitController::class, 'index']);//->middleware('auth')
Route::post('/git/pull', [GitController::class, 'pull'])->name('git.pull');
Route::post('/serve', [GitController::class, 'serve'])->name('git.serve');
Route::post('/clear-messages', [GitController::class, 'clearMessages'])->name('git.clearMessages');