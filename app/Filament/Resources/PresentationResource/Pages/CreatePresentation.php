<?php

namespace App\Filament\Resources\PresentationResource\Pages;

use App\Filament\Resources\PresentationResource;
use App\Traits\FilamentRedirect;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePresentation extends CreateRecord
{
    use FilamentRedirect;

    protected static string $resource = PresentationResource::class;
}
