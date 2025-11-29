<?php

namespace App\Filament\Resources\AdditionalDocuments\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AdditionalDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Dosya ismi')
                    ->autofocus()
                    ->required()
                    ->columnSpanFull(),

                FileUpload::make('file')
                    ->label('Dosya')
                    ->required()
                    ->directory('additional-documents')
                    ->columnSpanFull(),
            ]);
    }
}
