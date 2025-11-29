<?php

namespace App\Filament\Resources\Publishers\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Publishers\PublisherResource;
use App\Traits\FilamentRedirect;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPublisher extends EditRecord
{
    use FilamentRedirect;

    protected static string $resource = PublisherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
