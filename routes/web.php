<?php

use App\Http\Controllers\GitController;
use App\Http\Controllers\TerminalController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('home');
// });

Route::get('/', [GitController::class, 'index']);//->middleware('auth')
Route::post('/git/pull', [GitController::class, 'pull'])->name('git.pull');
Route::post('/serve', [GitController::class, 'serve'])->name('git.serve');