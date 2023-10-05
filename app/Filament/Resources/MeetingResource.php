<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeetingResource\Pages;
use App\Models\Meeting;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                    ->required(),

                TextInput::make('order')
                    ->label('Sıra')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(function () {
                        return Meeting::max('order') + 1;
                    }),

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
                        CheckboxList::make('participants')
                            ->label('Katılımcılar')
                            ->options(User::active()
                                ->pluck('name', 'id')
                                ->toArray())
                            ->bulkToggleable(),

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
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListMeetings::route('/'),
            'create' => Pages\CreateMeeting::route('/create'),
            'edit' => Pages\EditMeeting::route('/{record}/edit'),
        ];
    }
}
