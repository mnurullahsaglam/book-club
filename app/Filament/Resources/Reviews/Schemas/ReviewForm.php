<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Mokhosh\FilamentRating\Components\Rating;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->hidden()
                    ->required(),

                Select::make('book_id')
                    ->relationship('book', 'name')
                    ->hidden()
                    ->required(),

                Rating::make('rating')
                    ->label('Puan')
                    ->required(),

                Textarea::make('comment')
                    ->label('Yorum')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }
}
