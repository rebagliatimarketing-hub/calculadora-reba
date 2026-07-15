<?php

use App\Modules\Approvals\Controllers\ApprovalController;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Calendar\Controllers\CalendarController;
use App\Modules\Conflicts\Controllers\ConflictController;
use App\Modules\Dashboard\Controllers\DashboardController;
use App\Modules\Launches\Controllers\LaunchController;
use App\Modules\Reports\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('launches', LaunchController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/launches/{launch}/submit-approval', [LaunchController::class, 'submitApproval'])->name('launches.submit-approval');
    Route::post('/launches/{launch}/approve', [LaunchController::class, 'approve'])->name('launches.approve');
    Route::post('/launches/{launch}/reject', [LaunchController::class, 'reject'])->name('launches.reject');
    Route::post('/launches/{launch}/regenerate-sessions', [LaunchController::class, 'regenerateSessions'])->name('launches.regenerate-sessions');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'events']);
    Route::post('/events/{event}/preview-schedule', [LaunchController::class, 'previewSchedule']);
    Route::post('/events/{event}/preview-conflicts', [LaunchController::class, 'previewConflicts']);
    Route::get('/conflicts', [ConflictController::class, 'index'])->name('conflicts.index');
    Route::post('/conflicts/{conflict}/resolve', [ConflictController::class, 'resolve'])->name('conflicts.resolve');
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
});
