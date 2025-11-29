<?php

namespace App\Filament\Resources\Writers;

use App\Filament\Resources\Writers\Pages\CreateWriter;
use App\Filament\Resources\Writers\Pages\EditWriter;
use App\Filament\Resources\Writers\Pages\ListWriters;
use App\Filament\Resources\Writers\Schemas\WriterForm;
use App\Filament\Resources\Writers\Schemas\WriterTable;
use App\Models\Writer;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class WriterResource extends Resource
{
    protected static ?string $model = Writer::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static ?string $slug = 'writers';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Yazar';

    protected static ?string $pluralLabel = 'Yazarlar';

    public static function form(Schema $schema): Schema
    {
        return WriterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WriterTable::configure($table);
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
