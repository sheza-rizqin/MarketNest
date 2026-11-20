<?php

use App\Http\Controllers\BrowseController;
use App\Http\Controllers\BuyerOrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ListingImageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicListingController;
use App\Http\Controllers\SavedListingsController;
use App\Http\Controllers\SellerDashboardController;
use App\Http\Controllers\SellerEnquiryController;
use App\Http\Controllers\SellerOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/categories', [BrowseController::class, 'categories'])->name('categories.index');
Route::get('/category/{category}/{subcategory?}', [BrowseController::class, 'category'])
    ->name('categories.show');
Route::get('/city/{city}', [BrowseController::class, 'city'])->name('cities.show');
Route::get('/city/{city}/{category}/{subcategory?}', [BrowseController::class, 'cityCategory'])
    ->name('cities.categories.show');

Route::get('/listings', [PublicListingController::class, 'index'])->name('listings.index');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/items/{listing}', [CartController::class, 'store'])->name('cart.items.store');
Route::delete('/cart/items/{listingId}', [CartController::class, 'destroy'])
    ->whereNumber('listingId')
    ->name('cart.items.destroy');
Route::post('/listings/{listing}/contact', [EnquiryController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('listings.contact');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [SellerDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/listings', [SellerDashboardController::class, 'myListings'])->name('dashboard.listings');
    Route::get('/dashboard/enquiries', [SellerEnquiryController::class, 'index'])->name('dashboard.enquiries');
    Route::get('/dashboard/orders', [BuyerOrderController::class, 'index'])->name('dashboard.orders');
    Route::get('/dashboard/sales', [SellerOrderController::class, 'index'])->name('dashboard.sales');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::patch('/dashboard/orders/{order}/cancel', [BuyerOrderController::class, 'cancel'])
        ->name('dashboard.orders.cancel');
    Route::patch('/dashboard/sales/{order}/status', [SellerOrderController::class, 'updateStatus'])
        ->name('dashboard.sales.status');
    Route::get('/favorites', [SavedListingsController::class, 'index'])->name('favorites.index');
    Route::post('/listings/{listing}/favorite', [FavoriteController::class, 'toggle'])->name('listings.favorite');
    Route::resource('listings', ListingController::class)->except(['index', 'show']);
    Route::patch('/listings/{listing}/status', [ListingController::class, 'updateStatus'])
        ->name('listings.status.update');
    Route::post('/listings/{listing}/images', [ListingImageController::class, 'store'])
        ->name('listings.images.store');
    Route::patch('/listings/{listing}/images/{image}/primary', [ListingImageController::class, 'setPrimary'])
        ->name('listings.images.primary');
    Route::patch('/listings/{listing}/images/{image}/order', [ListingImageController::class, 'reorder'])
        ->name('listings.images.reorder');
    Route::delete('/listings/{listing}/images/{image}', [ListingImageController::class, 'destroy'])
        ->name('listings.images.destroy');
});

Route::get('/listings/{listing}/images/{image}', [ListingImageController::class, 'show'])
    ->name('listings.images.show');
Route::get('/listings/{listing}', [PublicListingController::class, 'show'])->name('listings.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
