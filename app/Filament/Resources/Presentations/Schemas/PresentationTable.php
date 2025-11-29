<?php

namespace App\Filament\Resources\Presentations\Schemas;

use App\Models\Presentation;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PresentationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Kişi')
                    ->searchable()
                    ->sortable()
                    ->visible(fn ($livewire) => $livewire->activeTab === 'all'),

                IconColumn::make('is_recommended')
                    ->label('Öneriliyor mu?')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('make_recommended')
                        ->label('Önerilenlere Ekle')
                        ->icon('heroicon-o-check')
                        ->color('primary')
                        ->visible(fn (Presentation $presentation) => ! $presentation->is_recommended && $presentation->user_id === auth()->id())
                        ->action(fn (Presentation $presentation) => $presentation->update(['is_recommended' => true])),

                    Action::make('make_unrecommended')
                        ->label('Önerilenlerden Çıkar')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->visible(fn (Presentation $presentation) => $presentation->is_recommended && $presentation->user_id === auth()->id())
                        ->action(fn (Presentation $presentation) => $presentation->update(['is_recommended' => false])),

                    Action::make('view')
                        ->label('Dosyayı görüntüle')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn ($record) => $record?->file_url)
                        ->openUrlInNewTab(),
                ]),
            ]);
    }
}

