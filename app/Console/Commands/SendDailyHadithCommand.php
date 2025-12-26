<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\DailyHadithNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SendDailyHadithCommand extends Command
{
    protected $signature = 'hadith:send-daily';

    protected $description = 'Send daily hadith email to all active users';

    public function handle(): int
    {
        $hadiths = $this->loadHadiths();

        if (empty($hadiths)) {
            $this->error('Could not load hadiths from database/data.');

            return self::FAILURE;
        }

        $hadith = $this->getRandomHadith($hadiths);

        $this->info("Sending random hadith #{$hadith['hadith_id']} to active users...");

        $users = User::active()->get();

        if ($users->isEmpty()) {
            $this->warn('No active users found.');

            return self::SUCCESS;
        }

        $count = 0;

        foreach ($users as $user) {
            $user->notify(new DailyHadithNotification($hadith));
            $count++;
        }

        $this->info("Successfully sent daily hadith to {$count} users.");

        return self::SUCCESS;
    }

    private function loadHadiths(): array
    {
        $path = database_path('data/riyazus-salihin.json');

        if (! File::exists($path)) {
            return [];
        }

        $content = File::get($path);

        return json_decode($content, true) ?? [];
    }

    private function getRandomHadith(array $hadiths): array
    {
        return $hadiths[array_rand($hadiths)];
    }
}
