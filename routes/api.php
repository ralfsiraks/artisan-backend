<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DiscountCodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
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

Route::middleware('auth')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:sanctum']], function() {
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/user', [UserController::class, 'getUser']);
    Route::patch('/user', [UserController::class, 'updateUser']);
    Route::patch('/password', [UserController::class, 'updatePassword']);
    Route::delete('/delete', [UserController::class, 'deleteUser']);
    Route::post('/checkout', [CheckoutController::class, 'checkout']);
    Route::get('/stripe-session', [CheckoutController::class, 'getSession']);
    Route::get('/history', [HistoryController::class, 'getOrderHistory']);
    Route::get('/order/{id}', [HistoryController::class, 'getOrder']);
});

// routes/api.php
Route::post('/stripe/webhook', [CheckoutController::class, 'handleWebhook']);
Route::get('/code', [DiscountCodeController::class, 'checkCode']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/cart', [ProductController::class, 'getCart']);
Route::get('/catalog', [ProductController::class, 'getCatalog']);
Route::get('/product', [ProductController::class, 'getProduct']);

