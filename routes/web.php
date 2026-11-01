<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'index'])
    ->name('products.index');
