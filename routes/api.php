<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', 'API\AuthController@login')->name('login');
Route::post('/register', 'API\AuthController@register')->name('register');

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/account', '\App\Http\Controllers\AccountController@myAccount');
    Route::patch('/account/{account}/add-balance', '\App\Http\Controllers\AccountController@addBalance');

    Route::resource('/category', '\App\Http\Controllers\CategoryController');

    Route::get('/expense/aggregation', '\App\Http\Controllers\ExpenseController@aggregation');
    Route::resource('/expense', '\App\Http\Controllers\ExpenseController');

    Route::post('/logout', 'API\AuthController@logout')->name('logout');
});
