<?php

namespace App\Filament\Resources\Reviews\Schemas;

use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Mokhosh\FilamentRating\Columns\RatingColumn;

class ReviewTable
{
    public static function configure(Table $table): Table
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
}
