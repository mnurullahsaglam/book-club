<?php

namespace App\Filament\Resources\Books\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('writer_id')
                    ->relationship('writer', 'name')
                    ->label('Yazar')
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('İsim')
                            ->required()
                            ->autofocus()
                            ->autocapitalize(),
                    ]),

                Select::make('publisher_id')
                    ->relationship('publisher', 'name')
                    ->label('Yayınevi')
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('İsim')
                            ->required()
                            ->autofocus()
                            ->autocapitalize(),
                    ]),

                TextInput::make('name')
                    ->label('Kitap İsmi')
                    ->required()
                    ->maxLength(255),

                TextInput::make('page_count')
                    ->label('Sayfa Sayısı')
                    ->numeric()
                    ->minValue(0),

                TextInput::make('publication_location')
                    ->label('Basım Yeri')
                    ->maxLength(255),

                TextInput::make('publication_date')
                    ->label('Basım Yılı')
                    ->numeric()
                    ->minValue(0),

                FileUpload::make('image')
                    ->label('Kapak Resmi')
                    ->image()
                    ->directory('books')
                    ->columnSpanFull(),

                Toggle::make('is_finished')
                    ->label('Okundu mu?')
                    ->default(false),
            ]);
    }
}

