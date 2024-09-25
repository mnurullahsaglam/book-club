<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WriterResource\Pages\CreateWriter;
use App\Filament\Resources\WriterResource\Pages\EditWriter;
use App\Filament\Resources\WriterResource\Pages\ListWriters;
use App\Models\Writer;
use App\Notifications\WriterSummaryNotification;
use App\Tables\Columns\ProgressColumn;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

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
                //
            ])
            ->actions([
                Action::make('export')
                    ->label('Özetini Çıkar')
                    ->color('info')
                    ->icon('heroicon-o-document')
                    ->action(function (Writer $record) {
                        auth()->user()->notify(new WriterSummaryNotification($record));

                        Notification::make()
                            ->title('Özet çıkarıldı')
                            ->body('Yazarın özeti e-posta ile gönderildi.')
                            ->icon('heroicon-o-document-check')
                            ->iconColor('info');
                    })
                    ->visible(fn (Writer $record) => $record->has('books')),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
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
