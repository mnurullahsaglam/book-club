<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdditionalDocumentResource\Pages\CreateAdditionalDocument;
use App\Filament\Resources\AdditionalDocumentResource\Pages\EditAdditionalDocument;
use App\Filament\Resources\AdditionalDocumentResource\Pages\ListAdditionalDocuments;
use App\Models\AdditionalDocument;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdditionalDocumentResource extends Resource
{
    protected static ?string $model = AdditionalDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Ek Dosya';

    protected static ?string $pluralLabel = 'Ek Dosyalar';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ->actions([
                Action::make('view')
                    ->label('Dosyayı görüntüle')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn($record) => $record->file_url, true),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
