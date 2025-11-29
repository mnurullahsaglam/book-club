<?php

namespace App\Filament\Resources\Reviews;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Models\Review;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Mokhosh\FilamentRating\Components\Rating;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-star';

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
                TextColumn::make('book.writer.name')
                    ->label('Yazar')
                    ->numeric()
                    ->sortable(),

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

                SelectFilter::make('users')
                    ->label('Kullanıcılar')
                    ->relationship('user', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->indicateUsing(function ($state) {
                        $userNames = User::whereIn('id', $state['values'])
                            ->orderBy('name')
                            ->get()
                            ->map(function ($user) {
                                return $user->name;
                            })
                            ->toArray();

                        $indicator = 'Kullanıcı: ';

                        $indicator .= implode(', ', $userNames);

                        if (! $state['values']) {
                            return null;
                        }

                        return $indicator;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function form(Schema $schema): Schema
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
