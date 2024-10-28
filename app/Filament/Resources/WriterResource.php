<?php

namespace App\Filament\Resources;

use App\Filament\Exports\BookExporter;
use App\Filament\Exports\WriterExporter;
use App\Filament\Resources\WriterResource\Pages\CreateWriter;
use App\Filament\Resources\WriterResource\Pages\EditWriter;
use App\Filament\Resources\WriterResource\Pages\ListWriters;
use App\Models\Writer;
use App\Notifications\WriterSummaryNotification;
use App\Tables\Columns\ProgressColumn;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Query\Builder;

class WriterResource extends Resource
{
    protected static ?string $model = Writer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $slug = 'writers';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Yazar';

    protected static ?string $pluralLabel = 'Yazarlar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('İsim')
                    ->required()
                    ->maxLength(255)
                    ->autofocus()
                    ->columnSpanFull(),

                FileUpload::make('image')
                    ->label('Fotoğraf')
                    ->image()
                    ->directory('writers')
                    ->columnSpanFull(),

                RichEditor::make('bio')
                    ->label('Biyografi')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),

                DatePicker::make('birth_date')
                    ->label('Doğum Tarihi'),

                DatePicker::make('death_date')
                    ->label('Ölüm Tarihi'),

                TextInput::make('birth_place')
                    ->label('Doğum Yeri')
                    ->maxLength(255),

                TextInput::make('death_place')
                    ->label('Ölüm Yeri')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    ->form([
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
                    ->form([
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
                    ->form([
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
            ->actions([
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
            ->bulkActions([
                DeleteBulkAction::make(),
                ExportBulkAction::make()
                    ->exporter(WriterExporter::class)
                    ->fileName(fn (Export $export): string => "Okuma Grubu Yazar Listesi")
                    ->label('Dışa Aktar: Yazarlar'),
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
            'index' => ListWriters::route('/'),
            'create' => CreateWriter::route('/create'),
            'edit' => EditWriter::route('/{record}/edit'),
        ];
    }
}
