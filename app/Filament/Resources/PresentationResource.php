<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PresentationResource\Pages\CreatePresentation;
use App\Filament\Resources\PresentationResource\Pages\EditPresentation;
use App\Filament\Resources\PresentationResource\Pages\ListPresentations;
use App\Models\Presentation;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class PresentationResource extends Resource
{
    protected static ?string $model = Presentation::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $slug = 'presentation';

    protected static ?string $recordTitleAttribute = 'meeting.title';

    protected static ?string $modelLabel = 'Sunum';

    protected static ?string $pluralLabel = 'Sunumlar';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Kişi')
                    ->relationship('user', 'name', fn(Builder $query) => $query->active())
                    ->required(),

                Select::make('meeting_id')
                    ->label('Toplantı')
                    ->relationship('meeting', 'title')
                    ->required(),

                TextInput::make('title')
                    ->label('Başlık')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                FileUpload::make('file')
                    ->label('Dosya')
                    ->required()
                    ->directory('presentations')
                    ->columnSpanFull(),

                RichEditor::make('description')
                    ->label('Açıklama')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('meeting.title')
                    ->label('Toplantı')
                    ->numeric()
                    ->sortable(),

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
            ->actions([
                Action::make('view')
                    ->label('Dosyayı görüntüle')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn($record) => Storage::url($record->file), true),
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
            'index' => ListPresentations::route('/'),
            'create' => CreatePresentation::route('/create'),
            'edit' => EditPresentation::route('/{record}/edit'),
        ];
    }
}
