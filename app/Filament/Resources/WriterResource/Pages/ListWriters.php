<?php

namespace App\Filament\Resources\WriterResource\Pages;

use App\Filament\Resources\WriterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWriters extends ListRecords
{
    protected static string $resource = WriterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
