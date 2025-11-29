<?php

namespace App\Filament\Resources\Books;

use Filament\Schemas\Schema;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ExportBulkAction;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use App\Filament\Exports\BookExporter;
use App\Filament\Resources\Books\Pages\CreateBook;
use App\Filament\Resources\Books\Pages\EditBook;
use App\Filament\Resources\Books\Pages\ListBooks;
use App\Filament\Resources\Books\Pages\ViewBook;
use App\Models\Book;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $slug = 'books';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Kitap';

    protected static ?string $pluralLabel = 'Kitaplar';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make([
                    Section::make([
                        TextEntry::make('name')
                            ->label('Kitap')
                            ->columnSpan(3),

                        TextEntry::make('writer.name')
                            ->label('Yazar')
                            ->columnSpan(3),

                        TextEntry::make('publisher.name')
                            ->label('Yayınevi')
                            ->columnSpan(2),

                        TextEntry::make('page_count')
                            ->label('Sayfa Sayısı')
                            ->columnSpan(2),

                        TextEntry::make('reviews.rating')
                            ->label('Ortalama Puan')
                            ->formatStateUsing(fn (Book $record) => number_format($record->reviews()->whereNotNull('rating')->avg('rating'), 1, ','))
                            ->columnSpan(2),
                    ])
                        ->columns(6),
                ])
                    ->columnSpan(4),

                Group::make([
                    Section::make([
                        ImageEntry::make('image')
                            ->label('Kapak Resmi')
                            ->columnSpanFull(),
                    ]),
                ])
                    ->columnSpan(1),
            ])
            ->columns(5);
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
