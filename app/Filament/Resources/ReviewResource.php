<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages\ListReviews;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Mokhosh\FilamentRating\Components\Rating;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $slug = 'reviews';

    protected static ?string $modelLabel = 'Değerlendirme';

    protected static ?string $pluralLabel = 'Değerlendirmeler';

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->when(auth()->user()->email !== 'nurullahsl87@gmail.com', function (Builder $query) {
                $query->where('user_id', auth()->id());
            }))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('book.name')
                    ->label('Kitap')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Kullanıcı')
                    ->sortable(),

                RatingColumn::make('rating')
                    ->label('Puan')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('not_entered')
                    ->label('Puan verilmemiş')
                    ->query(fn (Builder $query): Builder => $query->whereNull('rating'))
                    ->default(),

                Filter::make('user_ids')
                    ->form([
                        Select::make('user_ids')
                            ->relationship('user', 'name')
                            ->label('Kullanıcı')
                            ->searchable()
                            ->preload()
                            ->multiple()
                            ->placeholder('Kullanıcı seçiniz'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['user_ids'], function ($query) use ($data) {
                            $query->whereIn('user_id', $data['user_ids']);
                        });
                    })
                    ->indicateUsing(function (array $data) {
                        if (! $data['user_ids']) {
                            return null;
                        }

                        return 'Kullanıcı: '.implode(', ', $data['user_ids']);
                    }),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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

                Forms\Components\Textarea::make('comment')
                    ->label('Yorum')
                    ->maxLength(65535)
                    ->columnSpanFull(),
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
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
