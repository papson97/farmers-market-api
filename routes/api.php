<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\FarmerController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\RepaymentController;

// Routes publiques
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Gestion des utilisateurs
    Route::middleware('role:admin')->group(function () {
        Route::post('/supervisors', [UserController::class, 'storeSupervisor']);
    });

    Route::middleware('role:admin,supervisor')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::post('/operators', [UserController::class, 'storeOperator']);
    });

    // Catégories - admin et supervisor
    Route::middleware('role:admin,supervisor')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    });

    // Catégories - tous les rôles peuvent lire
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    // Produits - admin et supervisor
    Route::middleware('role:admin,supervisor')->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    });

    // Produits - tous les rôles peuvent lire
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // Fermiers - tous les rôles
    Route::get('/farmers/search', [FarmerController::class, 'search']);
    Route::get('/farmers', [FarmerController::class, 'index']);
    Route::get('/farmers/{id}', [FarmerController::class, 'show']);
    Route::post('/farmers', [FarmerController::class, 'store']);
    Route::put('/farmers/{id}', [FarmerController::class, 'update']);

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
    Route::middleware('role:operator,supervisor,admin')->group(function () {
        Route::post('/transactions', [TransactionController::class, 'store']);
    });

    // Dettes et remboursements
    Route::get('/farmers/{farmerId}/debts', [RepaymentController::class, 'debts']);
    Route::get('/farmers/{farmerId}/repayments', [RepaymentController::class, 'index']);
    Route::post('/repayments', [RepaymentController::class, 'store']);

});