<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Auth::routes();

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/tentang', [PageController::class, 'tentang'])->name('tentang');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
