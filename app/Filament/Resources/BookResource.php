<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages\CreateBook;
use App\Filament\Resources\BookResource\Pages\EditBook;
use App\Filament\Resources\BookResource\Pages\ListBooks;
use App\Filament\Resources\BookResource\Pages\ViewBook;
use App\Models\Book;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $slug = 'books';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Kitap';

    protected static ?string $pluralLabel = 'Kitaplar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('writer_id')
                    ->relationship('writer', 'name')
                    ->label('Yazar')
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('İsim')
                            ->required()
                            ->autofocus()
                            ->autocapitalize(),
                    ]),

                Select::make('publisher_id')
                    ->relationship('publisher', 'name')
                    ->label('Yayınevi')
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('İsim')
                            ->required()
                            ->autofocus()
                            ->autocapitalize(),
                    ]),

                TextInput::make('name')
                    ->label('Kitap İsmi')
                    ->required()
                    ->maxLength(255),

                TextInput::make('page_count')
                    ->label('Sayfa Sayısı')
                    ->numeric()
                    ->minValue(0),

                TextInput::make('publication_location')
                    ->label('Basım Yeri')
                    ->maxLength(255),

                TextInput::make('publication_date')
                    ->label('Basım Yılı')
                    ->numeric()
                    ->minValue(0),

                FileUpload::make('image')
                    ->label('Kapak Resmi')
                    ->image()
                    ->directory('books')
                    ->columnSpanFull(),

                Toggle::make('is_finished')
                    ->label('Okundu mu?')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('İsim')
                    ->searchable(),

                TextColumn::make('writer.name')
                    ->label('Yazar')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('publisher.name')
                    ->label('Yayınevi')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('reviews_avg_rating')
                    ->avg('reviews', 'rating')
                    ->label('Ortalama Puan')
                    ->numeric()
                    ->searchable(),

                ToggleColumn::make('is_finished')
                    ->label('Okundu mu?')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Group::make([
                    Section::make([
                        TextEntry::make('name')
                            ->label('Kitap')
                            ->columnSpan(2),

                        TextEntry::make('writer.name')
                            ->label('Yazar')
                            ->columnSpan(2),

                        TextEntry::make('publisher.name')
                            ->label('Yayınevi')
                            ->columnSpan(2),

                        TextEntry::make('page_count')
                            ->label('Sayfa Sayısı')
                            ->columnSpan(3),

                        TextEntry::make('reviews.rating')
                            ->label('Ortalama Puan')
                            ->formatStateUsing(fn (Book $record) => number_format($record->reviews()->whereNotNull('rating')->avg('rating'), 1, ','))
                            ->columnSpan(3),
                    ])
                        ->columns(6),
                ])
                    ->columnSpan(3),

                Group::make([
                    Section::make([
                        ImageEntry::make('image')
                            ->label('Kapak Resmi')
                            ->columnSpanFull()
                    ])
                ])
                    ->columnSpan(1)
            ])
            ->columns(4);
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
            'index' => ListBooks::route('/'),
            'create' => CreateBook::route('/create'),
            'edit' => EditBook::route('/{record}/edit'),
            'view' => ViewBook::route('/{record}'),
        ];
    }
}
