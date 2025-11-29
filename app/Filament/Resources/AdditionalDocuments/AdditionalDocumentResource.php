<?php

namespace App\Filament\Resources\AdditionalDocuments;

use App\Filament\Resources\AdditionalDocuments\Pages\CreateAdditionalDocument;
use App\Filament\Resources\AdditionalDocuments\Pages\EditAdditionalDocument;
use App\Filament\Resources\AdditionalDocuments\Pages\ListAdditionalDocuments;
use App\Filament\Resources\AdditionalDocuments\Schemas\AdditionalDocumentForm;
use App\Filament\Resources\AdditionalDocuments\Schemas\AdditionalDocumentTable;
use App\Models\AdditionalDocument;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AdditionalDocumentResource extends Resource
{
    protected static ?string $model = AdditionalDocument::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Ek Dosya';

    protected static ?string $pluralLabel = 'Ek Dosyalar';

    public static function form(Schema $schema): Schema
    {
        return AdditionalDocumentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdditionalDocumentTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdditionalDocuments::route('/'),
            'create' => CreateAdditionalDocument::route('/create'),
            'edit' => EditAdditionalDocument::route('/{record}/edit'),
        ];
    }
}
