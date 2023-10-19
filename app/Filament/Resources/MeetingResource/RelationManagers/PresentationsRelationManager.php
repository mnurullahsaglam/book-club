<?php

namespace App\Filament\Resources\MeetingResource\RelationManagers;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PresentationsRelationManager extends RelationManager
{
    protected static string $relationship = 'presentations';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Sunum';

    protected static ?string $pluralLabel = 'Sunumlar';

    protected static ?string $title = 'Sunumlar';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->relationship('user', 'name', fn(Builder $query) => $query->active())
                    ->label('Kişi')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('title')
                    ->label('Başlık')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                FileUpload::make('file')
                    ->label('Dosya')
                    ->required()
                    ->directory('presentations')
                    ->disk('public')
                    ->columnSpanFull(),

                RichEditor::make('description')
                    ->label('Açıklama')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Kişi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Açıklama'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                Action::make('view')
                    ->label('Dosyayı görüntüle')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn($record) => $record->file_url)
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
