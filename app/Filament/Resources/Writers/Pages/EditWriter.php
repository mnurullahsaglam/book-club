<?php

namespace App\Filament\Resources\Writers\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Writers\WriterResource;
use App\Traits\FilamentRedirect;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWriter extends EditRecord
{
    use FilamentRedirect;

    protected static string $resource = WriterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
