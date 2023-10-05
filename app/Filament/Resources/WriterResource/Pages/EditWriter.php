<?php

namespace App\Filament\Resources\WriterResource\Pages;

use App\Filament\Resources\WriterResource;
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
            Actions\DeleteAction::make(),
        ];
    }
}
