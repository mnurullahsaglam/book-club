<?php

namespace App\Console\Commands;

use App\Models\Meeting;
use App\Models\User;
use App\Notifications\MeetingSummaryNotification;
use Illuminate\Console\Command;

class MeetingSummaryCommand extends Command
{
    protected $signature = 'meeting:summary';

    protected $description = 'Command description';

    public function handle(): void
    {
        $latestMeeting = Meeting::latest()->first();

        if ($latestMeeting) {
            $users = User::active()->get();

            foreach ($users as $user) {
                $user->notify(new MeetingSummaryNotification($latestMeeting));
            }
        }
    }
}
