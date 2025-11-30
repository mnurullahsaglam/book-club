<?php

namespace App\Filament\Resources\Writers\Schemas;

use App\Filament\Exports\WriterExporter;
use App\Models\Writer;
use App\Notifications\WriterSummaryNotification;
use App\Tables\Columns\ProgressColumn;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Query\Builder;

class WriterTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('books', 'readBooks')->withCount('books'))
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('İsim')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('books_count')
                    ->counts('books')
                    ->label('Kitap Sayısı')
                    ->sortable(),

                ProgressColumn::make('reading_progress')
                    ->label('Kitap İlerlemesi'),

                ToggleColumn::make('is_finished')
                    ->label('Bitti mi?')
                    ->disabled(static function ($record) {
                        return $record->books_count === 0;
                    }),
            ])
            ->filters([
                Filter::make('birth_date_range')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Doğum Tarihi Başlangıcı'),
                        DatePicker::make('to')
                            ->label('Doğum Tarihi Bitişi'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['from'], function ($query) use ($data) {
                            $query->whereDate('birth_date', '>=', $data['from']);
                        })->when($data['to'], function ($query) use ($data) {
                            $query->whereDate('birth_date', '<=', $data['to']);
                        });
                    })
                    ->indicateUsing(function (array $data) {
                        if (! $data['from'] && ! $data['to']) {
                            return null;
                        }

                        return 'Doğum Tarihi Aralığı: '.($data['from'] ?? '...').' - '.($data['to'] ?? '...');
                    }),

                Filter::make('death_date_range')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Ölüm Tarihi Başlangıcı'),
                        DatePicker::make('to')
                            ->label('Ölüm Tarihi Bitişi'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['from'], function ($query) use ($data) {
                            $query->whereDate('death_date', '>=', $data['from']);
                        })->when($data['to'], function ($query) use ($data) {
                            $query->whereDate('death_date', '<=', $data['to']);
                        });
                    })
                    ->indicateUsing(function (array $data) {
                        if (! $data['from'] && ! $data['to']) {
                            return null;
                        }

                        return 'Ölüm Tarihi Aralığı: '.($data['from'] ?? '...').' - '.($data['to'] ?? '...');
                    }),

                Filter::make('finished')
                    ->schema([
                        Checkbox::make('is_finished')
                            ->label('Bitti mi?'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['is_finished'], function (Builder $query) use ($data) {
                            $query->where('is_finished', $data['is_finished']);
                        });
                    })
                    ->indicateUsing(function (array $data) {
                        if (! $data['is_finished']) {
                            return null;
                        }

                        return 'Kitapları Bitenler';
                    }),
            ])
            ->recordActions([
                Action::make('send-summary')
                    ->label('Özetini Çıkar')
                    ->color('info')
                    ->icon('heroicon-o-document')
                    ->action(function (Writer $record) {
                        auth()->user()->notify(new WriterSummaryNotification($record));

                        Notification::make()
                            ->title('Özet çıkarıldı')
                            ->body('Yazarın özeti e-posta ile gönderildi.')
                            ->icon('heroicon-o-document-check')
                            ->iconColor('info')
                            ->send();
                    })
                    ->visible(fn (Writer $record) => $record->has('books')),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
                ExportBulkAction::make()
                    ->exporter(WriterExporter::class)
                    ->fileName(fn (Export $export): string => 'Okuma Grubu Yazar Listesi')
                    ->label('Dışa Aktar: Yazarlar'),
            ]);
    }
}
