<?php

use App\Http\Controllers\V1\API\AccountsController;
use App\Http\Controllers\V1\API\AuthController;
use App\Http\Controllers\V1\API\BalancesController;
use App\Http\Controllers\V1\API\BrandsController;
use App\Http\Controllers\V1\API\CashiersController;
use App\Http\Controllers\V1\API\PaymentsController;
use App\Http\Controllers\V1\API\ProfileController;
use App\Http\Controllers\V1\API\RechargesController;
use App\Http\Controllers\V1\API\ReportsController;
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

Route::middleware('config')->group(function ()
{
    Route::prefix('/auth')->group(function ()
    {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgotten', [AuthController::class, 'forgotten']);
        Route::post('/validate', [AuthController::class, 'valid_token']);
        Route::post('/code', [AuthController::class, 'code']);
        Route::post('/renew', [AuthController::class, 'renew']);
    });

    Route::middleware('auth.bearer')->group(function ()
    {
        Route::prefix('/brands')->group(function ()
        {
            Route::get('/', [BrandsController::class, 'index']);
            Route::post('/', [BrandsController::class, 'store']);

            Route::get('/{type}', [BrandsController::class, 'indexByType']);

            Route::prefix('/{brand}')->group(function ()
            {
                Route::get('/', [BrandsController::class, 'show']);
                Route::put('/', [BrandsController::class, 'update']);
                Route::delete('/', [BrandsController::class, 'destroy']);

                Route::prefix('/products')->group(function ()
                {
                    Route::get('/', [BrandsController::class, 'productsByBrand']);
                });
            });
        });

        Route::prefix('/recharges')->group(function ()
        {
            Route::post('/', [RechargesController::class, 'create']);
            Route::get('/search/{uuid}/{attemps}', [RechargesController::class, 'search']);
        });

        Route::prefix('/reports')->group(function ()
        {
            Route::prefix('/sales')->group(function ()
            {
                Route::get('/', [ReportsController::class, 'sales']);
            });

            Route::prefix('/purchases')->group(function ()
            {
                Route::get('/', [ReportsController::class, 'purchases']);
            });
        });

        Route::prefix('/balance')->group(function ()
        {
            Route::get('/', [BalancesController::class, 'show']);
        });

        Route::prefix('/users')->group(function ()
        {
            Route::prefix('/password')->group(function ()
            {
                Route::put('/', [AuthController::class, 'changePassword']);
            });

            Route::prefix('/push')->group(function ()
            {
                Route::post('/', [ProfileController::class, 'storePush']);
            });
        });

        Route::prefix('/accounts')->group(function ()
        {
            Route::prefix('/methods')->group(function ()
            {
                Route::get('/{method}', [AccountsController::class, 'accountsBymethod']);
            });
        });

        Route::prefix('/payments')->group(function ()
        {
            Route::post('/', [PaymentsController::class, 'store']);
        });

        Route::prefix('/cashiers')->group(function ()
        {
            Route::get('/', [CashiersController::class, 'index']);
            Route::post('/', [CashiersController::class, 'store']);

            Route::prefix('{user}')->group(function ()
            {
                Route::put('/status', [CashiersController::class, 'updateStatus']);
                Route::put('/blocked', [CashiersController::class, 'updateBlocked']);
                Route::put('/password', [CashiersController::class, 'updatePassword']);
                Route::put('/', [CashiersController::class, 'update']);
            });
        });

        Route::prefix('/profile')->group(function ()
        {
            Route::get('/', [ProfileController::class, 'show']);
            Route::put('/', [ProfileController::class, 'update']);
        });
    });
});
