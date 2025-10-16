<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\VoucherImportController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RedemptionController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Home - Redirect to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

/*
|--------------------------------------------------------------------------
| Public Voucher Redemption Routes (No Authentication Required)
|--------------------------------------------------------------------------
| Simple one-click redemption flow:
| 1. User visits /redemption?code=ABC123
| 2. Code auto-fills in form
| 3. User clicks "Redeem Voucher Now"
| 4. Voucher status updated to 2 (redeemed) with timestamp
| 5. Barcode and 15-minute timer displayed
*/
Route::prefix('redemption')->name('redemption.')->group(function () {
    // Show redemption form with auto-filled code
    Route::get('/', [RedemptionController::class, 'showRedemptionForm'])->name('form');
    
    // Submit redemption (one-click redeem)
    Route::post('/redeem', [RedemptionController::class, 'redeemVoucher'])->name('submit');
    
    // Check timer status (AJAX)
    Route::post('/check-timer', [RedemptionController::class, 'checkTimer'])->name('check-timer');
});

// Alternative URL pattern for direct voucher redemption links
// Example: https://app.rewardly.com/redeem-voucher/ABC123
Route::get('/redeem-voucher/{code}', function($code) {
    return redirect()->route('redemption.form', ['code' => $code]);
})->name('redeem.voucher');

// Legacy redemption routes (for backward compatibility)
Route::get('/redeem', function () {
    return redirect()->route('redemption.form');
})->name('redeem.form');

Route::post('/redeem', function () {
    return redirect()->route('redemption.form');
})->name('redeem.process');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    
    // Logout
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout.post');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    /*
    |--------------------------------------------------------------------------
    | Voucher Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('vouchers')->name('vouchers.')->group(function () {
        
        // Main CRUD routes
        Route::get('/', [VoucherController::class, 'index'])->name('index');
        Route::get('/create', [VoucherController::class, 'create'])->name('create');
        Route::post('/', [VoucherController::class, 'store'])->name('store');
        Route::get('/show/{id}', [VoucherController::class, 'show'])->name('show');
        Route::get('/edit/{id}', [VoucherController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [VoucherController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [VoucherController::class, 'destroy'])->name('destroy');
        
        // Bulk operations
        Route::post('/bulk-delete', [VoucherController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('/bulk', [VoucherController::class, 'storeBulk'])->name('store-bulk');
        
        // Export & Statistics
        Route::get('/export/csv', [VoucherController::class, 'export'])->name('export');
        Route::get('/statistics', [VoucherController::class, 'statistics'])->name('statistics');
        
        // Validation
        Route::post('/check-code', [VoucherController::class, 'checkCode'])->name('check-code');
        
        /*
        |--------------------------------------------------------------------------
        | Voucher Import System Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('import')->name('import.')->group(function () {
            
            // Import form page
            Route::get('/', [VoucherImportController::class, 'showImportForm'])->name('upload-bulk-vouchers');
            
            // Process import upload
            Route::post('/', [VoucherImportController::class, 'import'])->name('upload');
            
            // Import history page
            Route::get('/history', [VoucherImportController::class, 'historyPage'])->name('history');
            
            // Import details page
            Route::get('/{identifier}/details', [VoucherImportController::class, 'showDetails'])->name('details');
            
            // Export redemption links to Excel
            Route::get('/{identifier}/export-links', [VoucherImportController::class, 'exportRedemptionLinks'])->name('export-links');
            
            // Get import progress (AJAX)
            Route::get('/{identifier}/progress', [VoucherImportController::class, 'progress'])->name('progress');
            
            // Get vouchers from import (AJAX)
            Route::get('/{identifier}/vouchers', [VoucherImportController::class, 'vouchers'])->name('vouchers');
            
            // Cancel import
            Route::post('/{identifier}/cancel', [VoucherImportController::class, 'cancel'])->name('cancel');
            
            // Delete import
            Route::delete('/{identifier}', [VoucherImportController::class, 'delete'])->name('delete');
        });
    });
    
    /*
    |--------------------------------------------------------------------------
    | Merchant Management Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('merchants', MerchantController::class);
    
    // Merchant specific import history
    Route::get('/merchants/{merchantId}/imports', [VoucherImportController::class, 'merchantHistory'])
        ->name('merchants.imports');
    
    /*
    |--------------------------------------------------------------------------
    | User Management Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('users', UserController::class);

    /*
    |--------------------------------------------------------------------------
    | Other Authenticated Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', function () {
        return view('pages.profile');
    })->name('profile');
    
    Route::get('/messages', function () {
        return view('pages.messages');
    })->name('messages');
    
    Route::get('/tasks', function () {
        return view('pages.tasks');
    })->name('tasks');
    
    Route::get('/faqs', function () {
        return view('pages.faqs');
    })->name('faqs');
    
    Route::get('/settings', function () {
        return view('pages.settings');
    })->name('settings');
    
    Route::get('/lock-screen', function () {
        return view('pages.lock-screen');
    })->name('lock-screen');
});

/*
|--------------------------------------------------------------------------
| Route Logging for Debugging (Remove in production)
|--------------------------------------------------------------------------
*/
\Illuminate\Support\Facades\Log::info('Routes file loaded', [
    'timestamp' => '2025-10-16 08:30:26',
    'user_login' => 'AriffAzmi',
    'total_routes' => count(Route::getRoutes()),
    'environment' => app()->environment(),
    'app_url' => config('app.url'),
]);