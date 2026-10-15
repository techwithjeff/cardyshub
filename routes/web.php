<?php

use App\Http\Controllers\Account\AddressController;
use App\Http\Controllers\Account\DashboardController;
use App\Http\Controllers\Account\OrderController as AccountOrderController;
use App\Http\Controllers\Account\WishlistController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WholesaleController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product:slug}', [ProductController::class, 'show'])->name('shop.show');

Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
Route::get('/brands/{brand:slug}', [BrandController::class, 'show'])->name('brands.show');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');

// Policy and information pages. Paths match the links already published in the footer.
Route::get('/delivery', [PageController::class, 'delivery'])->name('delivery');
Route::get('/returns', [PageController::class, 'returns'])->name('returns');
Route::get('/faqs', [PageController::class, 'faqs'])->name('faqs');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/cookies', [PageController::class, 'cookies'])->name('cookies');
Route::get('/wholesale', [PageController::class, 'wholesale'])->name('wholesale');
Route::post('/wholesale', [WholesaleController::class, 'store'])->name('wholesale.store')->middleware('throttle:5,1');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:5,1');
Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.store')->middleware('throttle:5,1');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/items/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store')->middleware('throttle:10,1');
Route::get('/checkout/{order:order_number}/confirmation', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

Route::middleware('auth')->post('/shop/{product:slug}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
Route::middleware('auth')->post('/wishlist/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

Route::middleware('auth')->get('/account', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/orders', [AccountOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order:order_number}', [AccountOrderController::class, 'show'])->name('orders.show');
    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::patch('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
});

Route::middleware('auth')->prefix('account')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
