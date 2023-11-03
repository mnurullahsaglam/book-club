<?php

namespace App\Filament\Resources\AdditionalDocumentResource\Pages;

use App\Filament\Resources\AdditionalDocumentResource;
use App\Traits\FilamentRedirect;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdditionalDocument extends EditRecord
{
    use FilamentRedirect;

    protected static string $resource = AdditionalDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
