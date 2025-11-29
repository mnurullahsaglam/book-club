<?php

namespace App\Filament\Resources\Writers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WriterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('İsim')
                    ->required()
                    ->maxLength(255)
                    ->autofocus()
                    ->columnSpanFull(),

                FileUpload::make('image')
                    ->label('Fotoğraf')
                    ->image()
                    ->directory('writers')
                    ->columnSpanFull(),

                RichEditor::make('bio')
                    ->label('Biyografi')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),

                DatePicker::make('birth_date')
                    ->label('Doğum Tarihi'),

                DatePicker::make('death_date')
                    ->label('Ölüm Tarihi'),

                TextInput::make('birth_place')
                    ->label('Doğum Yeri')
                    ->maxLength(255),

                TextInput::make('death_place')
                    ->label('Ölüm Yeri')
                    ->maxLength(255),
            ]);
    }
}
