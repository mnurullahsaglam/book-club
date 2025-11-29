<?php

namespace App\Console\Commands;

use App\Models\Meeting;
use App\Notifications\PresentationReminderNotification;
use Illuminate\Console\Command;

class MeetingReminderCommand extends Command
{
    protected $signature = 'meeting:reminder';

    protected $description = 'Send reminder notifications to users who have presentations for meetings that are 3 days and 1 day away';

    public function handle(): void
    {
        $today = now()->startOfDay();

        $meetingsInThreeDays = Meeting::query()
            ->whereDate('date', $today->copy()->addDays(3))
            ->with(['presentations.user'])
            ->get();

        $meetingsInOneDay = Meeting::query()
            ->whereDate('date', $today->copy()->addDays(1))
            ->with(['presentations.user'])
            ->get();

        $totalSent = 0;

        foreach ($meetingsInThreeDays as $meeting) {
            if ($meeting->presentations->isEmpty()) {
                $this->info("No presentations found for meeting: {$meeting->title} (3 days away)");

                continue;
            }

            $presentationsByUser = $meeting->presentations->groupBy('user_id');

            foreach ($presentationsByUser as $userId => $presentations) {
                $user = $presentations->first()->user;

                if ($user && $user->is_active) {
                    $user->notify(new PresentationReminderNotification($meeting, $presentations, 3));
                    $totalSent++;
                    $this->info("Sent 3-day reminder to {$user->name} for meeting: {$meeting->title} ({$presentations->count()} presentation(s))");
                }
            }
        }

        foreach ($meetingsInOneDay as $meeting) {
            if ($meeting->presentations->isEmpty()) {
                $this->info("No presentations found for meeting: {$meeting->title} (1 day away)");

                continue;
            }

            $presentationsByUser = $meeting->presentations->groupBy('user_id');

            foreach ($presentationsByUser as $userId => $presentations) {
                $user = $presentations->first()->user;

                if ($user && $user->is_active) {
                    $user->notify(new PresentationReminderNotification($meeting, $presentations, 1));
                    $totalSent++;
                    $this->info("Sent 1-day reminder to {$user->name} for meeting: {$meeting->title} ({$presentations->count()} presentation(s))");
                }
            }
        }

        if ($totalSent === 0) {
            $this->info('No users with presentations found for reminders today.');
        } else {
            $this->info("Total notifications sent: {$totalSent}");
        }
    }
}
