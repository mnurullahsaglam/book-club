<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages\EditReview;
use App\Filament\Resources\ReviewResource\Pages\ListReviews;
use App\Filament\Resources\ReviewResource\RelationManagers;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use IbrahimBougaoua\FilamentRatingStar\Actions\RatingStar;
use IbrahimBougaoua\FilamentRatingStar\Columns\RatingStarColumn;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $slug = 'reviews';

    protected static ?string $modelLabel = 'Yorum';

    protected static ?string $pluralLabel = 'Yorumlar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),

                Select::make('book_id')
                    ->relationship('book', 'name')
                    ->required(),

                RatingStar::make()
                    ->label('Rating'),

                Forms\Components\Textarea::make('comment')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('book.name')
                    ->label('Kitap')
                    ->numeric()
                    ->sortable(),

                RatingStarColumn::make('rating')
                    ->label('Puan')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->visible(fn(Review $review) => $review->user_id === auth()->id()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReviews::route('/'),
            'edit' => EditReview::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
