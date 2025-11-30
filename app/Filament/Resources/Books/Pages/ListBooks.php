<?php

namespace App\Filament\Resources\Books\Pages;

use App\Filament\Exports\BookExporter;
use App\Filament\Imports\BookImporter;
use App\Filament\Resources\Books\BookResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Models\Export;
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
            ExportAction::make()
                ->exporter(BookExporter::class)
                ->fileName(fn (Export $export): string => 'Okuma Grubu Kitap Listesi')
                ->label('Dışa Aktar: Kitaplar'),
            CreateAction::make(),
        ];
    }
}
