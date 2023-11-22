<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use App\Traits\FilamentRedirect;
use Filament\Actions;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBook extends EditRecord
{
    use FilamentRedirect;

    protected static string $resource = BookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->color('info'),
            DeleteAction::make(),
        ];
    }
}
