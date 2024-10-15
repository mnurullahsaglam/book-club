<?php

use App\Http\Controllers\MeetingPdfExportController;
use App\Models\Presentation;

Route::get('/meetings/{meeting}/export/pdf', MeetingPdfExportController::class)
    ->name('meetings.export.pdf')
    ->middleware('auth');

Route::get('/presentation/{presentation}', function (Presentation $presentation) {
    return response()->file($presentation->file_url);
})
    ->name('presentation.show')
    ->middleware('auth');
