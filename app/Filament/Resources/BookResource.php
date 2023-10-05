<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages\CreateBook;
use App\Filament\Resources\BookResource\Pages\EditBook;
use App\Filament\Resources\BookResource\Pages\ListBooks;
use App\Filament\Resources\BookResource\RelationManagers;
use App\Models\Book;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
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
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required(),
                    ]),

                Select::make('publisher_id')
                    ->relationship('publisher', 'name')
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required(),
                    ]),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('page_count')
                    ->numeric()
                    ->minValue(0),

                FileUpload::make('image')
                    ->image()
                    ->directory('books')
                    ->columnSpanFull(),
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

                TextColumn::make('page_count')
                    ->label('Sayfa Sayısı')
                    ->numeric()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
            'index' => ListBooks::route('/'),
            'create' => CreateBook::route('/create'),
            'edit' => EditBook::route('/{record}/edit'),
        ];
    }
}
