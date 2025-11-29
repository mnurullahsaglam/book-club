<?php

namespace App\Filament\Widgets;

use App\Models\Meeting;
use App\Models\User;
use Filament\Widgets\ChartWidget;

class MeetingParticipantsChart extends ChartWidget
{
    protected ?string $heading = 'Katılım Durumu';

    protected static ?int $sort = 3;

    protected ?string $pollingInterval = null;

    public function getDescription(): ?string
    {
        return Meeting::past()->count().' toplantı yapıldı.';
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

    public function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'max' => 10, // TODO: could be dynamic
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
