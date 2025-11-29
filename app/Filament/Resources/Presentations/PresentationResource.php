<?php

namespace App\Filament\Resources\Presentations;

use App\Filament\Resources\Presentations\Pages\ListPresentations;
use App\Filament\Resources\Presentations\Schemas\PresentationForm;
use App\Filament\Resources\Presentations\Schemas\PresentationTable;
use App\Models\Presentation;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PresentationResource extends Resource
{
    protected static ?string $model = Presentation::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $slug = 'presentation';

    protected static ?string $recordTitleAttribute = 'meeting.title';

    protected static ?string $modelLabel = 'Sunum';

    protected static ?string $pluralLabel = 'Sunumlar';

    public static function form(Schema $schema): Schema
    {
        return PresentationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PresentationTable::configure($table);
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
            'index' => ListPresentations::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
