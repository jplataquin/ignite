<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TicketTypeController;
use App\Http\Controllers\Admin\TicketTypeCategoryController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\TicketStatusController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ChunkUploadController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Password reset for force reset (must bypass RedirectIfPasswordNeedsReset in its implementation)
    Route::get('/reset-password', [AuthController::class, 'showResetTemp'])->name('password.reset.temp');
    Route::post('/reset-password', [AuthController::class, 'resetTemp']);

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Ticket Routes
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::post('/tickets/upload-chunk', [ChunkUploadController::class, 'upload'])->name('tickets.upload-chunk');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/accept', [TicketController::class, 'accept'])->name('tickets.accept');
    Route::get('/api/categories', [TicketController::class, 'getCategories'])->name('api.categories');
    Route::get('/api/users', [TicketController::class, 'getUsers'])->name('api.users');

    // Admin-only Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // User Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
        Route::post('/users/{user}/reject', [UserController::class, 'reject'])->name('users.reject');

        // Ticket Type Management
        Route::resource('ticket-types', TicketTypeController::class)->except(['show']);
        Route::get('ticket-types/{ticket_type}/categories', [TicketTypeCategoryController::class, 'index'])->name('ticket-types.categories.index');
        Route::post('ticket-types/{ticket_type}/categories', [TicketTypeCategoryController::class, 'store'])->name('ticket-types.categories.store');

        // Role Management
        Route::resource('roles', RoleController::class)->except(['show']);

        // Division Management
        Route::resource('divisions', DivisionController::class)->except(['show']);

        // Department Management
        Route::resource('departments', DepartmentController::class)->except(['show']);

        // Status Management
        Route::resource('ticket-statuses', TicketStatusController::class)->except(['show']);

        // System Settings Management
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
