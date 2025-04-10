<?php

namespace App\Console\Commands;

use App\Models\Meeting;
use App\Notifications\PresentationReminderNotification;
use Illuminate\Console\Command;

class PresentationReminderCommand extends Command
{
    protected $signature = 'presentation:reminder';

    protected $description = 'This command will send a reminder notification to users who have a presentation in a meeting scheduled for the next day.';

    public function handle(): void
    {
        $nextMeeting = Meeting::query()
            ->whereBetween('date', [now(), now()->addDay()])
            ->first();

        if ($nextMeeting) {
            $presentations = $nextMeeting->presentations()
                ->with('user')
                ->get()
                ->groupBy('user_id');

            foreach ($presentations as $userPresentations) {
                $user = $userPresentations->first()->user;

                $user->notify(new PresentationReminderNotification($userPresentations));
            }
        }
    }
}
