<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuestBookingController;
use App\Http\Controllers\GuestAuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\CheckOutController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExtraServiceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AmenityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
// ── Public Guest Booking ──────────────────────────────────────────────────────
Route::get('/book',             [GuestBookingController::class, 'index'])->name('guest.booking');
Route::get('/book/rooms',       [GuestBookingController::class, 'availableRooms'])->name('guest.rooms');

// Guest auth
Route::get('/book/login',       [GuestAuthController::class, 'showLogin'])->name('guest.login');
Route::post('/book/login',      [GuestAuthController::class, 'login'])->name('guest.login.post');
Route::get('/book/register',    [GuestAuthController::class, 'showRegister'])->name('guest.register');
Route::post('/book/register',   [GuestAuthController::class, 'register'])->name('guest.register.post');
Route::post('/book/logout',     [GuestAuthController::class, 'logout'])->name('guest.logout');

// Guest protected routes (must be logged in as customer)
Route::middleware('auth:customer')->group(function () {
    Route::post('/book/intent',     [GuestBookingController::class, 'createIntent'])->name('guest.intent');
    Route::get('/book/complete',    [GuestBookingController::class, 'complete'])->name('guest.complete');
    Route::get('/book/history',     [GuestBookingController::class, 'history'])->name('guest.history');
});

Route::get('/', fn() => redirect()->route('guest.booking'));
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Stripe webhook – bypasses CSRF + session (verified by Stripe signature)
// Skipping StartSession prevents DB session-lock timeouts on the single-threaded server.
Route::post('/payments/webhook', [PaymentController::class, 'webhook'])
    ->name('payments.webhook')
    ->withoutMiddleware([
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ]);

/*
|--------------------------------------------------------------------------
| Authenticated – admin + staff
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'changePassword'])->name('profile.password');
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rooms – read + status patch for all staff
    Route::get('/rooms',                [RoomController::class, 'index'])->name('rooms.index');

    // Room types – read
    Route::get('/room-types',             [RoomTypeController::class, 'index'])->name('room-types.index');
    Route::get('/room-types/{roomType}',  [RoomTypeController::class, 'show'])->name('room-types.show');

    // Customers
    Route::get('/customers',                  [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create',           [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers',                 [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}',       [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/edit',  [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}',       [CustomerController::class, 'update'])->name('customers.update');

    // Bookings
    Route::get('/bookings',                        [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create',                 [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings',                       [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/available-rooms',        [BookingController::class, 'availableRooms'])->name('bookings.available-rooms');
    Route::get('/bookings/{booking}',              [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{booking}/edit',         [BookingController::class, 'edit'])->name('bookings.edit');
    Route::put('/bookings/{booking}',              [BookingController::class, 'update'])->name('bookings.update');
    Route::post('/bookings/{booking}/confirm',     [BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::post('/bookings/{booking}/cancel',      [BookingController::class, 'cancel'])->name('bookings.cancel');

    // Check-In
    Route::get('/check-ins',            [CheckInController::class, 'index'])->name('check-ins.index');
    Route::get('/check-ins/create',     [CheckInController::class, 'create'])->name('check-ins.create');
    Route::post('/check-ins',           [CheckInController::class, 'store'])->name('check-ins.store');
    Route::get('/check-ins/{checkIn}',  [CheckInController::class, 'show'])->name('check-ins.show');

    // Check-Out
    Route::get('/check-outs',                               [CheckOutController::class, 'index'])->name('check-outs.index');
    Route::get('/check-outs/create',                        [CheckOutController::class, 'create'])->name('check-outs.create');
    Route::post('/check-outs',                              [CheckOutController::class, 'store'])->name('check-outs.store');
    Route::post('/check-outs/services',                     [CheckOutController::class, 'addService'])->name('check-outs.add-service');
    Route::delete('/check-outs/services/{bookingService}',  [CheckOutController::class, 'removeService'])->name('check-outs.remove-service');

    // Payments
    Route::get('/payments',                        [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create',                 [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments/cash',                  [PaymentController::class, 'cash'])->name('payments.cash');
    Route::post('/payments/intent',                [PaymentController::class, 'createIntent'])->name('payments.intent');
    Route::get('/payments/summary/{bookingId}',    [PaymentController::class, 'summary'])->name('payments.summary');
    Route::get('/payments/{payment}',              [PaymentController::class, 'show'])->name('payments.show');

    // Invoices
    Route::get('/invoices',                     [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}',           [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/print',     [InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('/invoices/{invoice}/pdf',       [InvoiceController::class, 'pdf'])->name('invoices.pdf');

    // Extra Services – read
    Route::get('/extra-services', [ExtraServiceController::class, 'index'])->name('extra-services.index');

    /*
    |----------------------------------------------------------------------
    | Admin-only
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {

        Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics');

        // Rooms – CRUD
        Route::get('/rooms/create',       [RoomController::class, 'create'])->name('rooms.create');
        Route::post('/rooms',             [RoomController::class, 'store'])->name('rooms.store');
        Route::get('/rooms/{room}/edit',  [RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/rooms/{room}',       [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{room}',    [RoomController::class, 'destroy'])->name('rooms.destroy');

        // Room Types – CRUD
        Route::get('/room-types/create',           [RoomTypeController::class, 'create'])->name('room-types.create');
        Route::post('/room-types',                 [RoomTypeController::class, 'store'])->name('room-types.store');
        Route::get('/room-types/{roomType}/edit',  [RoomTypeController::class, 'edit'])->name('room-types.edit');
        Route::put('/room-types/{roomType}',       [RoomTypeController::class, 'update'])->name('room-types.update');
        Route::delete('/room-types/{roomType}',    [RoomTypeController::class, 'destroy'])->name('room-types.destroy');

        // Amenities
        Route::get('/amenities',                  [AmenityController::class, 'index'])->name('amenities.index');
        Route::get('/amenities/create',           [AmenityController::class, 'create'])->name('amenities.create');
        Route::post('/amenities',                 [AmenityController::class, 'store'])->name('amenities.store');
        Route::get('/amenities/{amenity}/edit',   [AmenityController::class, 'edit'])->name('amenities.edit');
        Route::put('/amenities/{amenity}',        [AmenityController::class, 'update'])->name('amenities.update');
        Route::delete('/amenities/{amenity}',     [AmenityController::class, 'destroy'])->name('amenities.destroy');
        Route::post('/rooms/{room}/amenities',    [AmenityController::class, 'syncRoom'])->name('rooms.amenities.sync');

        // Extra Services – CRUD
        Route::get('/extra-services/create',              [ExtraServiceController::class, 'create'])->name('extra-services.create');
        Route::post('/extra-services',                    [ExtraServiceController::class, 'store'])->name('extra-services.store');
        Route::get('/extra-services/{extraService}/edit', [ExtraServiceController::class, 'edit'])->name('extra-services.edit');
        Route::put('/extra-services/{extraService}',      [ExtraServiceController::class, 'update'])->name('extra-services.update');
        Route::delete('/extra-services/{extraService}',   [ExtraServiceController::class, 'destroy'])->name('extra-services.destroy');

        // Customer delete
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        // Invoice management
        Route::post('/invoices/{invoice}/issue',   [InvoiceController::class, 'issue'])->name('invoices.issue');
        Route::post('/invoices/{invoice}/void',    [InvoiceController::class, 'void'])->name('invoices.void');
        Route::get('/invoices/{invoice}/discount', [InvoiceController::class, 'showDiscount'])->name('invoices.discount');
        Route::put('/invoices/{invoice}/discount', [InvoiceController::class, 'applyDiscount'])->name('invoices.discount.apply');

        // Payments
        Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');

        // Users
        Route::get('/users',              [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create',       [UserController::class, 'create'])->name('users.create');
        Route::post('/users',             [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}',       [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit',  [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}',       [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}',    [UserController::class, 'destroy'])->name('users.destroy');

        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

        // Reports
        Route::get('/reports/bookings/daily',   [ReportController::class, 'dailyBookings'])->name('reports.bookings.daily');
        Route::get('/reports/bookings/monthly', [ReportController::class, 'monthlyBookings'])->name('reports.bookings.monthly');
        Route::get('/reports/income',           [ReportController::class, 'income'])->name('reports.income');
    });

    Route::get('/rooms/{room}',         [RoomController::class, 'show'])->name('rooms.show');
    Route::get('/rooms/{room}/logs',    [RoomController::class, 'logs'])->name('rooms.logs');
    Route::post('/rooms/{room}/status', [RoomController::class, 'updateStatus'])->name('rooms.status');
});