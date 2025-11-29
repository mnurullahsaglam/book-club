<?php

namespace App\Filament\Resources\AdditionalDocuments\Schemas;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdditionalDocumentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Dosya ismi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('meeting.title')
                    ->label('Toplantı')
                    ->searchable()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Dosyayı görüntüle')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => $record->file_url)
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}

