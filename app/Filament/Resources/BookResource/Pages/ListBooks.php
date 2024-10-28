<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Imports\BookImporter;
use App\Filament\Resources\BookResource;
use Filament\Actions;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListBooks extends ListRecords
{
    protected static string $resource = BookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(BookImporter::class)
                ->label('İçe Aktar: Kitaplar'),
            Actions\CreateAction::make(),
        ];
    }
}
