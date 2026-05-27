<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\QuoteRequestController;
use App\Http\Controllers\StoreController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CoachMiddleware;

// ─── Public UI ──────────────────────────────────────────────────────────────
Route::get('/', function () { 
    $landingCollections = \App\Models\LandingCollection::where('is_active', true)
        ->orderBy('sort_order', 'asc')
        ->get();
    $testimonials = \App\Models\Testimonial::where('is_active', true)
        ->orderBy('sort_order', 'asc')
        ->limit(5)
        ->get();
    $heroSettings = [
        'subtitle'   => \App\Models\SiteSetting::where('key', 'hero_subtitle')->value('value') ?? 'Premium armor tailored for programs that demand greatness. Built for the modern athlete, delivered with lightning speed.',
        'media_path' => \App\Models\SiteSetting::where('key', 'hero_media_path')->value('value') ?? asset('images/hero-models.png'),
        'media_type' => \App\Models\SiteSetting::where('key', 'hero_media_type')->value('value') ?? 'image',
    ];
    return view('welcome', compact('landingCollections', 'testimonials', 'heroSettings')); 
});
Route::get('/testimonials', function () {
    $testimonials = \App\Models\Testimonial::where('is_active', true)
        ->orderBy('sort_order', 'asc')
        ->get();
    return view('testimonials.index', compact('testimonials'));
})->name('testimonials.index');
Route::get('/quote', [QuoteRequestController::class, 'show'])->name('quote.show');
Route::post('/quote', [QuoteRequestController::class, 'store'])->name('quote.store');
Route::get('/quote/success', function () { return view('quote_success'); })->name('quote.success');
Route::get('/agent/dashboard', function () { return view('agent.dashboard'); });
Route::get('/catalog', function () {
    $collections = \App\Models\DesignCollection::with('designs')
        ->orderBy('sort_order', 'asc')
        ->orderBy('name', 'asc')
        ->get()
        ->map(function ($col) {
        return (object)[
            'name' => $col->name,
            'image' => $col->image_path,
            'sports' => array_values(array_unique(array_merge(
                is_array($col->sports) ? $col->sports : [],
                $col->designs->pluck('sport')->filter()->unique()->toArray()
            )))
        ];
    });

    // Get orphaned designs (no collection)
    $orphanedDesigns = \App\Models\DesignCatalog::whereNull('design_collection_id')
        ->orderBy('sort_order', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();

    $allSports = config('sports.categories');

    return view('catalog.index', compact('collections', 'orphanedDesigns', 'allSports'));
})->name('catalog.index');

Route::get('/catalog/{collection}', function (\Illuminate\Http\Request $request, $collection) {
    $collectionModel = \App\Models\DesignCollection::where('name', $collection)->firstOrFail();
    
    $selectedSport = $request->query('sport');
    $selectedTypes = $request->query('types', []);
    if (!is_array($selectedTypes)) {
        $selectedTypes = explode(',', $selectedTypes);
    }
    $selectedTypes = array_filter($selectedTypes);
    
    $designCatalogQuery = \App\Models\DesignCatalog::where('design_collection_id', $collectionModel->id);
    
    if (!empty($selectedSport)) {
        $designCatalogQuery->where('sport', $selectedSport);
    }
    
    if (!empty($selectedTypes)) {
        $designCatalogQuery->where(function ($query) use ($selectedTypes) {
            foreach ($selectedTypes as $type) {
                $query->orWhere('type', $type)
                      ->orWhereJsonContains('types', $type);
            }
        });
    }
    
    $designCatalog = $designCatalogQuery->orderBy('sort_order', 'desc')->orderBy('created_at', 'desc')->get();
    
    $availableSports = \App\Models\DesignCatalog::where('design_collection_id', $collectionModel->id)
        ->whereNotNull('sport')
        ->where('sport', '!=', '')
        ->distinct()
        ->orderBy('sport')
        ->pluck('sport');
        
    // Extract distinct types available in this collection
    $allCollectionDesigns = \App\Models\DesignCatalog::where('design_collection_id', $collectionModel->id)->get(['type', 'types']);
    $availableTypeKeys = [];
    foreach ($allCollectionDesigns as $d) {
        if ($d->type) {
            $availableTypeKeys[] = $d->type;
        }
        if (is_array($d->types)) {
            $availableTypeKeys = array_merge($availableTypeKeys, $d->types);
        }
    }
    $availableTypeKeys = array_unique($availableTypeKeys);
        
    $collection = $collectionModel->name;
        
    return view('catalog.show', compact('designCatalog', 'availableSports', 'selectedSport', 'selectedTypes', 'collection', 'availableTypeKeys'));
})->name('catalog.show');

// Public Team Stores (parent-facing, no auth)
Route::get('/store/search', [StoreController::class, 'search'])->name('store.search');
Route::get('/store/{slug}', [StoreController::class, 'show'])->name('store.show');
Route::post('/store/{slug}/order', [StoreController::class, 'submitOrder'])->name('store.order.submit');

// ─── Authentication ──────────────────────────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── Internal Password Reset ─────────────────────────────────────────────────
Route::get('/forgot-password', [\App\Http\Controllers\InternalPasswordResetController::class, 'showVerifyForm'])->name('password.verify.form');
Route::get('/forgot-password/verify', function() { return redirect()->route('password.verify.form'); });
Route::post('/forgot-password/verify', [\App\Http\Controllers\InternalPasswordResetController::class, 'verifyIdentity'])->name('password.verify.submit');
Route::get('/forgot-password/reset', [\App\Http\Controllers\InternalPasswordResetController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/forgot-password/reset', [\App\Http\Controllers\InternalPasswordResetController::class, 'updatePassword'])->name('password.update');

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
    Route::post('/coach/item/{item}/markup', [CoachController::class, 'updateItemMarkup'])->name('coach.store.item.markup');
    Route::post('/coach/store/{store}/items/bulk-markup', [CoachController::class, 'updateBulkItemMarkup'])->name('coach.store.items.bulk-markup');
    Route::post('/coach/store/{store}/deadline', [CoachController::class, 'updateDeadline'])->name('coach.store.deadline');
    Route::get('/coach/store/{store}/export', [CoachController::class, 'exportOrderCSV'])->name('coach.store.export');
    Route::post('/coach/store/{store}/submit', [CoachController::class, 'submitMasterOrder'])->name('coach.store.submit');
    Route::post('/coach/store/{store}/reopen', [CoachController::class, 'reopenStore'])->name('coach.store.reopen');
    Route::post('/coach/store/{store}/approve-pricing', [CoachController::class, 'approvePricing'])->name('coach.store.pricing.approve');
    Route::post('/coach/store/{store}/cover', [CoachController::class, 'updateCoverImage'])->name('coach.store.cover');
    Route::post('/coach/profile/logo', [CoachController::class, 'updateProfileLogo'])->name('coach.profile.logo');

    // Coach can edit parent orders
    Route::get('/coach/order/{order}/edit', [CoachController::class, 'editOrder'])->name('coach.order.edit');
    Route::post('/coach/order/{order}/update', [CoachController::class, 'updateOrder'])->name('coach.order.update');
    Route::delete('/coach/order/{order}/delete', [CoachController::class, 'deleteOrder'])->name('coach.order.delete');

    // Direct Orders (No Team Store)
    Route::post('/coach/direct-order/submit', [CoachController::class, 'submitDirectOrder'])->name('coach.direct-order.submit');
    Route::post('/coach/direct-order/finalize', [CoachController::class, 'finalizeDirectOrders'])->name('coach.direct-order.finalize');
    Route::get('/coach/direct-order/export/{batchId}', [CoachController::class, 'exportDirectOrderBatch'])->name('coach.direct-order.export');
    Route::post('/coach/direct-order/archive/{batchId}', [CoachController::class, 'archiveDirectOrderBatch'])->name('coach.direct-order.archive');
});

// ─── Admin Portal ────────────────────────────────────────────────────────────
Route::middleware(['auth', AdminMiddleware::class])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Coach CRUD
    Route::get('/admin/coach/{user}/edit', [AdminController::class, 'editCoach'])->name('admin.coach.edit');
    Route::post('/admin/coach/{user}/update', [AdminController::class, 'updateCoach'])->name('admin.coach.update');
    Route::post('/admin/coach/{user}/reset-password', [AdminController::class, 'resetCoachPassword'])->name('admin.coach.reset-password');
    Route::delete('/admin/coach/{user}/delete', [AdminController::class, 'deleteCoach'])->name('admin.coach.delete');

    // Design catalog management
    Route::post('/admin/design/bulk-sort', [AdminController::class, 'bulkSortDesigns'])->name('admin.design.bulk-sort');
    Route::post('/admin/design/bulk-assign', [AdminController::class, 'bulkAssignDesigns'])->name('admin.design.bulk-assign');
    Route::post('/admin/design', [AdminController::class, 'createDesign'])->name('admin.design.create');
    Route::put('/admin/design/{design}', [AdminController::class, 'updateDesign'])->name('admin.design.update');
    Route::get('/admin/design/{design}', function () { return redirect()->route('admin.dashboard'); });
    Route::delete('/admin/design/{design}', [AdminController::class, 'deleteDesign'])->name('admin.design.delete');
    Route::post('/admin/design/{design}/assign-to-coach-profile', [AdminController::class, 'assignToCoachProfile'])->name('admin.design.assign-to-coach-profile');
    Route::get('/admin/design/{design}/assign-to-coach-profile', function () {
        return redirect()->route('admin.dashboard')->with('error', 'Your session expired or you refreshed a form submission. Please try assigning the design again.');
    });

    // Design collections management
    Route::post('/admin/design-collections', [AdminController::class, 'createDesignCollection'])->name('admin.design-collection.create');
    Route::post('/admin/design-collections/bulk-update', [AdminController::class, 'bulkUpdateDesignCollections'])->name('admin.design-collection.bulk-update');
    Route::put('/admin/design-collections/{collection}', [AdminController::class, 'updateDesignCollection'])->name('admin.design-collection.update');
    Route::delete('/admin/design-collections/{collection}', [AdminController::class, 'deleteDesignCollection'])->name('admin.design-collection.delete');

    // Collection Items Management
    Route::get('/admin/design-collections/{collection}/manage', [AdminController::class, 'manageCollection'])->name('admin.design-collection.manage');
    Route::post('/admin/design-collections/{collection}/add-item', [AdminController::class, 'addDesignToCollection'])->name('admin.design-collection.add-item');
    Route::post('/admin/design-collections/{collection}/remove-item/{design}', [AdminController::class, 'removeDesignFromCollection'])->name('admin.design-collection.remove-item');
    Route::post('/admin/design-collections/{collection}/bulk-sort', [AdminController::class, 'updateCollectionDesignsSort'])->name('admin.design-collection.bulk-sort');

    // Assign designs to coaches
    Route::post('/admin/coach/{coach}/assign-design', [AdminController::class, 'assignDesign'])->name('admin.coach.assign-design');
    Route::delete('/admin/coach/{coach}/remove-design/{design}', [AdminController::class, 'removeDesign'])->name('admin.coach.remove-design');

    // Store controls
    Route::post('/admin/stores', [AdminController::class, 'createCampaignStore'])->name('admin.stores.create');
    Route::post('/admin/stores/{store}/approve', [AdminController::class, 'approveStore'])->name('admin.stores.approve');
    Route::post('/admin/stores/{store}/decline', [AdminController::class, 'declineStore'])->name('admin.stores.decline');
    Route::post('/admin/stores/{store}/archive', [AdminController::class, 'archiveStore'])->name('admin.stores.archive');
    Route::post('/admin/stores/{store}/unarchive', [AdminController::class, 'unarchiveStore'])->name('admin.stores.unarchive');
    Route::delete('/admin/stores/{store}/delete', [AdminController::class, 'deleteArchivedStore'])->name('admin.stores.delete');
    Route::get('/admin/stores/{store}/edit', [AdminController::class, 'editStore'])->name('admin.store.edit');
    Route::get('/admin/direct-batch/{batch_id}', [AdminController::class, 'showDirectBatch'])->name('admin.direct-batch.show');
    Route::get('/admin/batch/{batch_id}/export', [AdminController::class, 'exportBatchCSV'])->name('admin.batch.export');
    Route::get('/admin/batch/{batch_id}/export-aggregate', [AdminController::class, 'exportBatchAggregationCSV'])->name('admin.batch.export-aggregate');
    Route::post('/admin/batch/{batch_id}/status', [AdminController::class, 'updateBatchStatus'])->name('admin.batch.status.update');
    Route::post('/admin/direct-batch/{batch_id}/mark-addressed', [AdminController::class, 'markDirectBatchAddressed'])->name('admin.direct-batch.mark-addressed');
    Route::post('/admin/store-batch/{batch_id}/mark-addressed', [AdminController::class, 'markStoreBatchAddressed'])->name('admin.store-batch.mark-addressed');
    Route::delete('/admin/archived-orders/{batch_id}', [AdminController::class, 'deleteArchivedOrderBatch'])->name('admin.archived-orders.delete');
    Route::post('/admin/stores/{store}/update', [AdminController::class, 'updateStore'])->name('admin.store.update');
    Route::post('/admin/stores/{store}/pricing', [AdminController::class, 'updateStorePricing'])->name('admin.store.pricing.update');
    Route::post('/admin/stores/{store}/cover', [AdminController::class, 'updateCoverImage'])->name('admin.store.cover');
    Route::post('/admin/stores/{store}/logo', [AdminController::class, 'updateStoreLogo'])->name('admin.store.logo');
    Route::post('/admin/stores/{store}/item', [AdminController::class, 'addStoreItem'])->name('admin.store.item.add');
    Route::post('/admin/stores/item/{item}/remove', [AdminController::class, 'removeStoreItem'])->name('admin.store.item.remove');
    Route::post('/admin/stores/{store}/package/{package}/attach', [AdminController::class, 'attachPackageComponent'])->name('admin.store.package.attach');
    Route::delete('/admin/stores/{store}/package/{package}/detach/{component}', [AdminController::class, 'detachPackageComponent'])->name('admin.store.package.detach');
    Route::get('/admin/stores/{store}/export', [AdminController::class, 'exportOrderCSV'])->name('admin.stores.export');

    // Order editing
    Route::get('/admin/order/{order}/edit', [AdminController::class, 'editOrder'])->name('admin.order.edit');
    Route::post('/admin/order/{order}/update', [AdminController::class, 'updateOrder'])->name('admin.order.update');
    Route::delete('/admin/order/{order}/delete', [AdminController::class, 'deleteOrder'])->name('admin.order.delete');

    // Landing Page Collections
    Route::post('/admin/landing-collections/bulk-sort', [AdminController::class, 'bulkSortLandingCollections'])->name('admin.landing.bulk-sort');
    Route::post('/admin/landing-collections', [AdminController::class, 'createCollection'])->name('admin.landing.create');
    Route::put('/admin/landing-collections/{collection}', [AdminController::class, 'updateCollection'])->name('admin.landing.update');
    Route::delete('/admin/landing-collections/{collection}', [AdminController::class, 'deleteCollection'])->name('admin.landing.delete');

    // Hero Settings
    Route::post('/admin/hero-settings', [AdminController::class, 'updateHeroSettings'])->name('admin.hero-settings.update');
    Route::post('/admin/hero-settings/remove-media', [AdminController::class, 'removeHeroMedia'])->name('admin.hero-settings.remove-media');

    // Testimonials
    Route::post('/admin/testimonials/bulk-sort', [AdminController::class, 'bulkSortTestimonials'])->name('admin.testimonials.bulk-sort');
    Route::post('/admin/testimonials', [AdminController::class, 'createTestimonial'])->name('admin.testimonials.create');
    Route::put('/admin/testimonials/{testimonial}', [AdminController::class, 'updateTestimonial'])->name('admin.testimonials.update');
    Route::delete('/admin/testimonials/{testimonial}', [AdminController::class, 'deleteTestimonial'])->name('admin.testimonials.delete');

    // Quotes
    Route::post('/admin/quote/{quoteRequest}/mark-addressed', [AdminController::class, 'markQuoteAddressed'])->name('admin.quote.mark-addressed');
});
