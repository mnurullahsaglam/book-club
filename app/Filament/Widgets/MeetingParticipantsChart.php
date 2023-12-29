<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class MeetingParticipantsChart extends ChartWidget
{
    protected static ?string $heading = 'Katılım Durumu';

    public function getDescription(): string|Htmlable|null
    {
        return 'Toplantıya katılmama sayısını gösterir.';
    }

    protected function getData(): array
    {
        $users = User::active()
            ->withCount(['meetings' => function ($query) {
                $query->where('is_participated', false);
            }])
            ->whereHas('meetings', function ($query) {
                $query->where('date', '<=', now());
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
