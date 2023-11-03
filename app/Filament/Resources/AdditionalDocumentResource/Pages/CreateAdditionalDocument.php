<?php

namespace App\Filament\Resources\AdditionalDocumentResource\Pages;

use App\Filament\Resources\AdditionalDocumentResource;
use App\Traits\FilamentRedirect;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAdditionalDocument extends CreateRecord
{
    use FilamentRedirect;

    protected static string $resource = AdditionalDocumentResource::class;
}
