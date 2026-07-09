<?php

use App\Modules\Calendar\Controllers\CalendarController;
use App\Modules\Launches\Controllers\LaunchController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/calendar/events', [CalendarController::class, 'events']);
    Route::post('/events/{event}/preview-schedule', [LaunchController::class, 'previewSchedule']);
    Route::post('/events/{event}/preview-conflicts', [LaunchController::class, 'previewConflicts']);
});
