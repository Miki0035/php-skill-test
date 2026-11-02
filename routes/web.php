<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;


Route::get('/', [ProductController::class, 'index'])
    ->name('products.index');

Route::post('/products', [ProductController::class, 'store'])
    ->name('products.store');

Route::put('/products/{id}', [ProductController::class, 'update'])
    ->name('products.update');
