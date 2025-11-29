<?php

namespace App\Filament\Resources\AdditionalDocuments;

use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\AdditionalDocuments\Pages\CreateAdditionalDocument;
use App\Filament\Resources\AdditionalDocuments\Pages\EditAdditionalDocument;
use App\Filament\Resources\AdditionalDocuments\Pages\ListAdditionalDocuments;
use App\Models\AdditionalDocument;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
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
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Dosya ismi')
                    ->autofocus()
                    ->required()
                    ->columnSpanFull(),

                FileUpload::make('file')
                    ->label('Dosya')
                    ->required()
                    ->directory('additional-documents')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Dosya ismi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('meeting.title')
                    ->label('Toplantı')
                    ->searchable()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Dosyayı görüntüle')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => $record->file_url)
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
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
