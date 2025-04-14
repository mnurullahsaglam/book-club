<?php

namespace App\Filament\Resources\BookResource\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Mokhosh\FilamentRating\Columns\RatingColumn;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $modelLabel = 'Değerlendirme';

    protected static ?string $pluralLabel = 'Değerlendirmeler';

    protected static ?string $title = 'Değerlendirmeler';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('comment')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('comment')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Kişi')
                    ->numeric()
                    ->sortable(),

                RatingColumn::make('rating')
                    ->label('Puan')
                    ->sortable(),

                TextColumn::make('comment')
                    ->label('Yorum'),
            ]);
    }
}
