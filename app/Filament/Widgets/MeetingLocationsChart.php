<?php

namespace App\Filament\Widgets;

use App\Models\Meeting;
use Filament\Widgets\ChartWidget;

class MeetingLocationsChart extends ChartWidget
{
    protected static ?string $heading = 'Mekânlar';

    protected static ?int $sort = 2;

    public function getDescription(): ?string
    {
        return Meeting::past()->count().' toplantı yapıldı.';
    }

    protected function getData(): array
    {
        $locations = Meeting::groupBy('location')->selectRaw('count(*) as total, location')->get();

        return [
            'datasets' => [
                [
                    'label' => 'Toplam',
                    'data' => $locations->pluck('total')->toArray(),
                    'backgroundColor' => [
                        '#FF0000', // Red
                        '#00FF00', // Green
                        '#0000FF', // Blue
                        '#FFFF00', // Yellow
                        '#FFA500', // Orange
                        '#800080', // Purple
                        '#FFC0CB', // Pink
                        '#00FFFF', // Cyan
                        '#A52A2A', // Brown
                        '#008080',  // Teal
                    ],
                ],
            ],
            'labels' => $locations->pluck('location')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
