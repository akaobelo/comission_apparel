<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\StoreController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CoachMiddleware;

// ─── Public UI ──────────────────────────────────────────────────────────────
Route::get('/', function () { 
    $landingCollections = \App\Models\LandingCollection::where('is_active', true)
        ->orderBy('sort_order', 'asc')
        ->get();
    return view('welcome', compact('landingCollections')); 
});
Route::get('/quote', function () { return view('quote'); });
Route::get('/agent/dashboard', function () { return view('agent.dashboard'); });

// Public Team Stores (parent-facing, no auth)
Route::get('/store/{slug}', [StoreController::class, 'show'])->name('store.show');
Route::post('/store/{slug}/order', [StoreController::class, 'submitOrder'])->name('store.order.submit');

// ─── Authentication ──────────────────────────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── Notifications (auth) ────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications/unread', [\App\Http\Controllers\NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
});

// ─── Coach Portal ────────────────────────────────────────────────────────────
Route::middleware(['auth', CoachMiddleware::class])->group(function () {
    Route::get('/coach/dashboard', [CoachController::class, 'dashboard'])->name('coach.dashboard');

    // Store management
    Route::post('/coach/store', [CoachController::class, 'createStore'])->name('coach.store.create');
    Route::post('/coach/store/{store}/item', [CoachController::class, 'addStoreItem'])->name('coach.store.item.add');
    Route::post('/coach/item/{item}/remove', [CoachController::class, 'removeStoreItem'])->name('coach.store.item.remove');
    Route::post('/coach/store/{store}/deadline', [CoachController::class, 'updateDeadline'])->name('coach.store.deadline');
    Route::post('/coach/store/{store}/submit', [CoachController::class, 'submitMasterOrder'])->name('coach.store.submit');
    Route::post('/coach/store/{store}/approve-pricing', [CoachController::class, 'approvePricing'])->name('coach.store.pricing.approve');

    // Coach can edit parent orders
    Route::get('/coach/order/{order}/edit', [CoachController::class, 'editOrder'])->name('coach.order.edit');
    Route::post('/coach/order/{order}/update', [CoachController::class, 'updateOrder'])->name('coach.order.update');
});

// ─── Admin Portal ────────────────────────────────────────────────────────────
Route::middleware(['auth', AdminMiddleware::class])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Coach CRUD
    Route::get('/admin/coach/{user}/edit', [AdminController::class, 'editCoach'])->name('admin.coach.edit');
    Route::post('/admin/coach/{user}/update', [AdminController::class, 'updateCoach'])->name('admin.coach.update');
    Route::delete('/admin/coach/{user}/delete', [AdminController::class, 'deleteCoach'])->name('admin.coach.delete');

    // Design catalog management
    Route::post('/admin/design', [AdminController::class, 'createDesign'])->name('admin.design.create');
    Route::delete('/admin/design/{design}', [AdminController::class, 'deleteDesign'])->name('admin.design.delete');
    Route::post('/admin/design/{design}/assign-to-store', [AdminController::class, 'assignToStore'])->name('admin.design.assign-to-store');

    // Assign designs to coaches
    Route::post('/admin/coach/{coach}/assign-design', [AdminController::class, 'assignDesign'])->name('admin.coach.assign-design');
    Route::delete('/admin/coach/{coach}/remove-design/{design}', [AdminController::class, 'removeDesign'])->name('admin.coach.remove-design');

    // Store controls
    Route::post('/admin/stores/{store}/approve', [AdminController::class, 'approveStore'])->name('admin.stores.approve');
    Route::post('/admin/stores/{store}/decline', [AdminController::class, 'declineStore'])->name('admin.stores.decline');
    Route::get('/admin/stores/{store}/edit', [AdminController::class, 'editStore'])->name('admin.store.edit');
    Route::post('/admin/stores/{store}/update', [AdminController::class, 'updateStore'])->name('admin.store.update');
    Route::post('/admin/stores/{store}/pricing', [AdminController::class, 'updateStorePricing'])->name('admin.store.pricing.update');
    Route::get('/admin/stores/{store}/export', [AdminController::class, 'exportOrderCSV'])->name('admin.stores.export');

    // Order editing
    Route::get('/admin/order/{order}/edit', [AdminController::class, 'editOrder'])->name('admin.order.edit');
    Route::post('/admin/order/{order}/update', [AdminController::class, 'updateOrder'])->name('admin.order.update');

    // Landing Page Collections
    Route::post('/admin/landing-collections', [AdminController::class, 'createCollection'])->name('admin.landing.create');
    Route::delete('/admin/landing-collections/{collection}', [AdminController::class, 'deleteCollection'])->name('admin.landing.delete');
});
