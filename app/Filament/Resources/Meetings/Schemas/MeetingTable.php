<?php

namespace App\Filament\Resources\Meetings\Schemas;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use App\Models\Meeting;

class MeetingTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable(),

                TextColumn::make('date')
                    ->label('Tarih')
                    ->date('d F Y')
                    ->sortable(),

                TextColumn::make('order')
                    ->label('Sıra')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('meetable.name')
                    ->label('Kitap/Yazar')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('date_range')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Tarihinden'),
                        DatePicker::make('to')
                            ->label('Tarihine'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['from'], function ($query) use ($data) {
                            $query->whereDate('date', '>=', $data['from']);
                        })->when($data['to'], function ($query) use ($data) {
                            $query->whereDate('date', '<=', $data['to']);
                        });
                    })
                    ->indicateUsing(function (array $data) {
                        if (! $data['from'] && ! $data['to']) {
                            return null;
                        }

                        return 'Tarih Aralığı: '.($data['from'] ?? '...').' - '.($data['to'] ?? '...');
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->color('warning'),
                    DeleteAction::make(),
                    Action::make('export')
                        ->label('PDF\'e Aktar')
                        ->color('info')
                        ->icon('heroicon-o-document')
                        ->url(fn (Meeting $record) => route('meetings.export.pdf', $record), true),
                ]),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}

