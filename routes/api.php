<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
use LaravelJsonApi\Laravel\Http\Controllers\JsonApiController;
use LaravelJsonApi\Laravel\Routing\Relationships;
use LaravelJsonApi\Laravel\Routing\ResourceRegistrar;

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

JsonApiRoute::server('v1')->prefix('v1')->resources(function (ResourceRegistrar $server): void {
    $server->resource('product-categories', JsonApiController::class)
        ->readOnly()
        ->relationships(function (Relationships $relations): void {
            $relations->hasMany('products')->readOnly();
            $relations->hasMany('multimedia')->readOnly();
        });

    $server->resource('products', JsonApiController::class)
        ->readOnly()
        ->relationships(function (Relationships $relations): void {
            $relations->hasOne('productCategory')->readOnly();
            $relations->hasMany('multimedia')->readOnly();
        });

    $server->resource('blog-post-categories', JsonApiController::class)
        ->readOnly()
        ->relationships(function (Relationships $relations): void {
            $relations->hasMany('blogPosts')->readOnly();
            $relations->hasMany('multimedia')->readOnly();
        });

    $server->resource('blog-posts', JsonApiController::class)
        ->readOnly()
        ->relationships(function (Relationships $relations): void {
            $relations->hasOne('blogPostCategory')->readOnly();
            $relations->hasMany('multimedia')->readOnly();
        });

    $server->resource('multimedia', JsonApiController::class)
        ->readOnly();
});
