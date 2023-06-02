<?php

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


Route::get('/login',[App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login');
Route::post('/adminlogin',[App\Http\Controllers\Admin\AuthController::class, 'loginAuthenticate'])->name('adminlogin');
Route::get('/logout',[App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');


Route::group(['middleware' => 'auth'], function () {
    
    Route::get('/', function () {
        return view('backend.dashboard');
    });

    Route::get('/dashboard',[App\Http\Controllers\Admin\AuthController::class, 'dashboard'])->name('dashboard');

    Route::get('/myaccount', function () {
        return view('backend.myaccount');
    });
   
    
});

