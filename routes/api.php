<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Blog\BlogPostController;
use App\Http\Controllers\Api\V1\Blog\FirstBlogPostController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\Product\ProductCategoryController;
use App\Http\Controllers\Api\V1\Product\ProductController;
use App\Http\Controllers\Api\V1\UserController;
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

Route::middleware('auth:sanctum')->get('/user', fn(Request $request) => $request->user());

Route::apiResource('/v1/users', UserController::class);

Route::get('/v1/first-blog-posts', FirstBlogPostController::class);

Route::apiResource('/v1/blog-posts', BlogPostController::class);

Route::apiResource('/v1/products', ProductController::class);

Route::apiResource('/v1/product-categories', ProductCategoryController::class);

Route::get('/v1/media', MediaController::class);
