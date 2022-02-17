<?php

use App\Http\Controllers\V1\TestsController;
use App\Http\Controllers\V1\WEB\ReportsController;
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

Route::get('/', function ()
{
    return response()->json([
        'message' => 'Welcome to the Mobirock API'
    ]);
});

Route::prefix('/tests')->group(function ()
{
    Route::get('/svbalance', [TestsController::class, 'svbalance']);
});

Route::prefix('/reports')->group(function ()
{
    Route::get('/{year}/{month}/{day}/{id}/{file}', [ReportsController::class, 'get']);
});
