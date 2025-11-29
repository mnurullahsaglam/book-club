<?php

namespace App\Filament\Resources\AdditionalDocuments\Pages;

use App\Filament\Resources\AdditionalDocuments\AdditionalDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdditionalDocuments extends ListRecords
{
    protected static string $resource = AdditionalDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
