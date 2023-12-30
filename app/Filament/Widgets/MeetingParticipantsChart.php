<?php

namespace App\Filament\Widgets;

use App\Models\Meeting;
use App\Models\User;
use Filament\Widgets\ChartWidget;

class MeetingParticipantsChart extends ChartWidget
{
    protected static ?string $heading = 'Katılım Durumu';

    protected static ?int $sort = 3;

    public function getDescription(): ?string
    {
        return Meeting::past()->count() . ' toplantı yapıldı.';
    }

    protected function getData(): array
    {
        $users = User::active()
            ->withCount(['meetings' => function ($query) {
                $query->where('is_participated', false);
            }])
            ->whereHas('meetings', function ($query) {
                $query->past();
            })
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Toplantıya Katılmama Sayısı',
                    'data' => $users->pluck('meetings_count')->toArray(),
                ],
            ],
            'labels' => $users->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
