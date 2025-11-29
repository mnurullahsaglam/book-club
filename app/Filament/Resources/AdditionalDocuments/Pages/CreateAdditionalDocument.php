<?php

namespace App\Filament\Resources\AdditionalDocuments\Pages;

use App\Filament\Resources\AdditionalDocuments\AdditionalDocumentResource;
use App\Traits\FilamentRedirect;
use Filament\Resources\Pages\CreateRecord;

class CreateAdditionalDocument extends CreateRecord
{
    use FilamentRedirect;

    protected static string $resource = AdditionalDocumentResource::class;
}
