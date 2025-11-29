<?php

namespace App\Filament\Resources\Meetings\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Meetings\MeetingResource;
use App\Filament\Resources\Meetings\Widgets\MeetingsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMeetings extends ListRecords
{
    protected static string $resource = MeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MeetingsOverview::class,
        ];
    }
}
