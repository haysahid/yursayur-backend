<?php

use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('auth/register', [UserController::class, 'register']);
Route::post('auth/login', [UserController::class, 'login']);

Route::apiResource('setting', 'SettingController');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserController::class, 'fetch']);
    Route::post('/profile', [UserController::class, 'updateProfile']);
    Route::post('/auth/logout', [UserController::class, 'logout']);

    Route::apiResource('store', 'StoreController');
    Route::apiResource('product', 'ProductController');
    Route::apiResource('product/{product_id}/image', 'ProductImageController');
    Route::apiResource('product/{product_id}/review', 'ReviewController');
    Route::apiResource('cart', 'CartItemController');
    Route::apiResource('order', 'OrderController');
});
