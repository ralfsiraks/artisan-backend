<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiscountCodes;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Laravel\Sanctum\Sanctum;

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

Route::get('/code', [DiscountCodes::class, 'checkCode']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [UserController::class, 'user'])->middleware('auth:sanctum');
Route::get('/cart', [ProductController::class, 'getCart']);
Route::get('/catalog/{category:title}', [ProductController::class, 'getCatalog']);
Route::get('/product', [ProductController::class, 'getProduct']);
