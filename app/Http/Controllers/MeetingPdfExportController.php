<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class MeetingPdfExportController extends Controller
{
    public function __invoke(Meeting $meeting)
    {
        return PDF::loadView('exports.pdf.meeting', [
            'meeting' => $meeting->loadMissing('users'),
            'notParticipatedUsers' => User::active()
                ->whereDoesntHave('meetings', function ($query) use ($meeting) {
                    $query->where('meeting_id', $meeting->id);
                })->get()])
            ->setPaper('a4')
//            ->save(public_path('exports/pdf/meetings/' . $meeting->book->writer->name . '-' . $meeting->order . '.pdf'))
            ->stream($meeting->date . '.pdf');
    }
}
