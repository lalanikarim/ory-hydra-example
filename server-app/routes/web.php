<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login',[LoginController::class,'login'])->name('login');
Route::post('/login',[LoginController::class,'authenticate']);
Route::get('/consent',[LoginController::class,'consent'])->name('consent');
Route::post('/consent',[LoginController::class,'approval']);
Route::get('/logout',[LoginController::class,'logout'])->name('logout');
Route::post('/logout',[LoginController::class,'endsession']);
