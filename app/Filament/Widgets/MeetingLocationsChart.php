<?php

namespace App\Filament\Widgets;

use App\Models\Meeting;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class MeetingLocationsChart extends ChartWidget
{
    protected static ?string $heading = 'Mekânlar';

    protected static ?int $sort = 2;

    public function getDescription(): ?string
    {
        return Meeting::past()->count() . ' toplantı yapıldı.';
    }

    protected function getData(): array
    {
        $locations = Meeting::groupBy('location')->selectRaw('count(*) as total, location')->get();

        return [
            'datasets' => [
                [
                    'label' => 'Blog posts created',
                    'data' => $locations->pluck('total')->toArray(),
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
