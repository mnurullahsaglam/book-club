<?php

namespace App\Filament\Resources\AdditionalDocuments\Pages;

use App\Filament\Resources\AdditionalDocuments\AdditionalDocumentResource;
use App\Traits\FilamentRedirect;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdditionalDocument extends EditRecord
{
    use FilamentRedirect;

    protected static string $resource = AdditionalDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
