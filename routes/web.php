<?php

use App\Http\Controllers\MeetingPdfExportController;

Route::get('/meetings/{meeting}/export/pdf', MeetingPdfExportController::class)
    ->name('meetings.export.pdf')
    ->middleware('auth');
