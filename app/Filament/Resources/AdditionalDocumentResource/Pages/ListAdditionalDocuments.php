<?php

namespace App\Filament\Resources\AdditionalDocumentResource\Pages;

use App\Filament\Resources\AdditionalDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdditionalDocuments extends ListRecords
{
    protected static string $resource = AdditionalDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
