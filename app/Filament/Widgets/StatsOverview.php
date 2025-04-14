<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\Meeting;
use App\Models\Presentation;
use App\Models\Writer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Toplantı Sayısı', Meeting::past()->count()),
            Stat::make('Yazar Sayısı', Writer::count()),
            Stat::make('Kitap Sayısı', Book::count()),
            Stat::make('Sayfa Sayısı', Book::whereNotNull('page_count')->sum('page_count')),
            Stat::make('Sunum Sayısı', Presentation::count()),
            Stat::make('Misafir Sayısı', Meeting::whereJsonLength('guests', '>', 0)->pluck('guests')->flatten()->unique()->count())
                ->description('Toplam misafir sayısı: '.Meeting::whereJsonLength('guests', '>', 0)->pluck('guests')->flatten()->count())
                ->chart(Meeting::past()->pluck('guests')->map(function ($guests) {
                    return count($guests);
                })->toArray())
                ->color('success'),
        ];
    }
}
