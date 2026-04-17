<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\AdminMiddleware;

// Public UI
Route::get('/', function () { return view('welcome'); });
Route::get('/quote', function () { return view('quote'); });
Route::get('/store/{slug}', [\App\Http\Controllers\StoreController::class, 'show'])->name('store.show');
Route::post('/store/{slug}/order', [\App\Http\Controllers\StoreController::class, 'submitOrder'])->name('store.order.submit');
Route::get('/agent/dashboard', function () { return view('agent.dashboard'); });

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Notifications
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications/unread', [\App\Http\Controllers\NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
});

// Coach Protected Routes
Route::middleware(['auth', \App\Http\Middleware\CoachMiddleware::class])->group(function () {
    Route::get('/coach/dashboard', [\App\Http\Controllers\CoachController::class, 'dashboard'])->name('coach.dashboard');
    Route::post('/coach/store', [\App\Http\Controllers\CoachController::class, 'createStore'])->name('coach.store.create');
    Route::post('/coach/store/{store}/item', [\App\Http\Controllers\CoachController::class, 'addStoreItem'])->name('coach.store.item.add');
    Route::post('/coach/item/{item}/remove', [\App\Http\Controllers\CoachController::class, 'removeStoreItem'])->name('coach.store.item.remove');
    Route::post('/coach/store/{store}/deadline', [\App\Http\Controllers\CoachController::class, 'updateDeadline'])->name('coach.store.deadline');
    Route::post('/coach/store/{store}/submit', [\App\Http\Controllers\CoachController::class, 'submitMasterOrder'])->name('coach.store.submit');
});

// Admin Protected Routes
Route::middleware(['auth', AdminMiddleware::class])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/users/{user}/approve', [AdminController::class, 'approveCoach'])->name('admin.users.approve');
    Route::post('/admin/users/{user}/decline', [AdminController::class, 'declineCoach'])->name('admin.users.decline');
    
    // Admin Store Controls
    Route::post('/admin/stores/{store}/approve', [AdminController::class, 'approveStore'])->name('admin.stores.approve');
    Route::post('/admin/stores/{store}/decline', [AdminController::class, 'declineStore'])->name('admin.stores.decline');
    Route::get('/admin/stores/{store}/export', [AdminController::class, 'exportOrderCSV'])->name('admin.stores.export');
});
