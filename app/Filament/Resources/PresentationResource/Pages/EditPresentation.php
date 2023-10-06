<?php

namespace App\Filament\Resources\PresentationResource\Pages;

use App\Filament\Resources\PresentationResource;
use App\Traits\FilamentRedirect;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPresentation extends EditRecord
{
    use FilamentRedirect;

    protected static string $resource = PresentationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
