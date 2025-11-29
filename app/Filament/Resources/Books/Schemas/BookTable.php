<?php

namespace App\Filament\Resources\Books\Schemas;

use App\Filament\Exports\BookExporter;
use App\Models\Book;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class BookTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['writer', 'publisher', 'reviews']))
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('İsim')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('writer.name')
                    ->label('Yazar')
                    ->numeric()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('publisher.name')
                    ->label('Yayınevi')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('reviews_avg_rating')
                    ->avg('reviews', 'rating')
                    ->label('Ortalama Puan')
                    ->description(fn (Book $record) => $record->reviews()->entered()->count().' değerlendirme')
                    ->numeric()
                    ->sortable(),

                ToggleColumn::make('is_finished')
                    ->label('Okundu mu?')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
                ExportBulkAction::make()
                    ->exporter(BookExporter::class)
                    ->fileName(fn (Export $export): string => 'Okuma Grubu Kitap Listesi')
                    ->label('Dışa Aktar: Kitaplar'),
            ]);
    }
}
