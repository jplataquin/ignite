<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TicketTypeController;
use App\Http\Controllers\Admin\TicketTypeCategoryController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\TicketStatusController;
use App\Http\Controllers\Admin\TicketPriorityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
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
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');

    // Admin-only Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // User Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');

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
    });
});
