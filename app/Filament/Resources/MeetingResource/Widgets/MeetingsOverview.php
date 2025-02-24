<?php

namespace App\Filament\Resources\MeetingResource\Widgets;

use App\Models\Book;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class MeetingsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('Book count'), $this->getBookCount()),
            Stat::make(__('Writer count'), $this->getWriterCount()),
            Stat::make(__('Publisher count'), $this->getPublisherCount()),
            Stat::make(__('Page count'), $this->getPageCount()),
        ];
    }

    private function getBookIds(): array
    {
        return DB::table('meetings')->where('meetable_type', Book::class)->distinct('meetable_id')->pluck('meetable_id')->toArray();
    }

    private function getBookCount(): int
    {
        return DB::table('meetings')->where('meetable_type', Book::class)->distinct('meetable_id')->count();
    }

    private function getWriterCount(): int
    {
        return DB::table('books')->whereIn('id', $this->getBookIds())->distinct('writer_id')->count();
    }

    private function getPublisherCount(): int
    {
        return DB::table('books')->whereIn('id', $this->getBookIds())->distinct('publisher_id')->count();
    }

    private function getPageCount(): int
    {
        return DB::table('books')->whereIn('id', $this->getBookIds())->sum('page_count');
    }
}
