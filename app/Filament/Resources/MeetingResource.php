<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeetingResource\Pages\CreateMeeting;
use App\Filament\Resources\MeetingResource\Pages\EditMeeting;
use App\Filament\Resources\MeetingResource\Pages\ListMeetings;
use App\Filament\Resources\MeetingResource\RelationManagers\PresentationsRelationManager;
use App\Models\Meeting;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MeetingResource extends Resource
{
    protected static ?string $model = Meeting::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $slug = 'meetings';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Toplantı';

    protected static ?string $pluralLabel = 'Toplantılar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('book_id')
                    ->label('Kitap')
                    ->relationship('book', 'name')
                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->writer->name} - {$record->name}")
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn(Set $set, ?string $state) => $set('order', Meeting::where('book_id', $state)->max('order') + 1)),

                TextInput::make('order')
                    ->label('Sıra')
                    ->required()
                    ->numeric()
                    ->minValue(1),

                TextInput::make('title')
                    ->label('Başlık')
                    ->required()
                    ->maxLength(255),

                TextInput::make('location')
                    ->label('Mekân')
                    ->required()
                    ->maxLength(255)
                    ->default('Kemah'),

                DatePicker::make('date')
                    ->label('Tarih')
                    ->default(now())
                    ->required(),

                Section::make('Gündem Maddeleri ve Kararlar')
                    ->schema([
                        Repeater::make('topics')
                            ->label('Gündem Maddeleri')
                            ->schema([
                                TextInput::make('topic')
                                    ->label('Madde')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->addActionLabel('Gündem maddesi ekle')
                            ->defaultItems(0),

                        Repeater::make('decisions')
                            ->label('Kararlar')
                            ->schema([
                                TextInput::make('decision')
                                    ->label('Karar')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->addActionLabel('Karar ekle')
                            ->defaultItems(0)
                    ]),

                Section::make('Katılımcılar')
                    ->schema([
                        CheckboxList::make('users')
                            ->label('Katılımcılar')
                            ->relationship('users', 'name', fn(Builder $query) => $query->active())
                            ->bulkToggleable()
                            ->live(),

                        Repeater::make('guests')
                            ->label('Konuklar')
                            ->schema([
                                TextInput::make('name')
                                    ->label('İsim')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->addActionLabel('Konuk ekle')
                            ->defaultItems(0)
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('book.name')
                    ->label('Kitap')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('order')
                    ->label('Sıra')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Başlık')
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
            PresentationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMeetings::route('/'),
            'create' => CreateMeeting::route('/create'),
            'edit' => EditMeeting::route('/{record}/edit'),
        ];
    }
}
