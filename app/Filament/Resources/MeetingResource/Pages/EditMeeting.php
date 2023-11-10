<?php

namespace App\Filament\Resources\MeetingResource\Pages;

use App\Filament\Resources\MeetingResource;
use App\Traits\FilamentRedirect;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMeeting extends EditRecord
{
    use FilamentRedirect;

    protected static string $resource = MeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->color('info'),
            DeleteAction::make(),
        ];
    }
}
