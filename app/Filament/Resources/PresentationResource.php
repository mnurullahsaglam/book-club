<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PresentationResource\Pages\ListPresentations;
use App\Models\Presentation;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PresentationResource extends Resource
{
    protected static ?string $model = Presentation::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $slug = 'presentation';

    protected static ?string $recordTitleAttribute = 'meeting.title';

    protected static ?string $modelLabel = 'Sunum';

    protected static ?string $pluralLabel = 'Sunumlar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Kişi')
                    ->relationship('user', 'name', fn (Builder $query) => $query->active())
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

                TextInput::make('Citation')
                    ->label('Künye')
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
                TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Kişi')
                    ->searchable()
                    ->sortable()
                    ->visible(fn ($livewire) => $livewire->activeTab === 'all'),

                IconColumn::make('is_recommended')
                    ->label('Öneriliyor mu?')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('make_recommended')
                        ->label('Önerilenlere Ekle')
                        ->icon('heroicon-o-check')
                        ->color('primary')
                        ->visible(fn (Presentation $presentation) => ! $presentation->is_recommended && $presentation->user_id === auth()->id())
                        ->action(fn (Presentation $presentation) => $presentation->update(['is_recommended' => true])),

                    Action::make('make_unrecommended')
                        ->label('Önerilenlerden Çıkar')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->visible(fn (Presentation $presentation) => $presentation->is_recommended && $presentation->user_id === auth()->id())
                        ->action(fn (Presentation $presentation) => $presentation->update(['is_recommended' => false])),

                    Action::make('view')
                        ->label('Dosyayı görüntüle')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn ($record) => $record?->file_url)
                        ->openUrlInNewTab(),
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
