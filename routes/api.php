<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('products', [ProductController::class, 'index'])->name('api.v1.products.index');
Route::get('products/topselling', [ProductController::class, 'topSelling'])->name('api.v1.products.topselling');
Route::get('orders/list_record', [OrderController::class, 'list_record'])->name('api.v1.orders.list_record');
Route::get('orders/{id}', [OrderController::class, 'show'])->name('api.v1.orders.show');