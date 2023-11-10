<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeetingResource\Pages\CreateMeeting;
use App\Filament\Resources\MeetingResource\Pages\EditMeeting;
use App\Filament\Resources\MeetingResource\Pages\ListMeetings;
use App\Filament\Resources\MeetingResource\Pages\ViewMeeting;
use App\Filament\Resources\MeetingResource\RelationManagers\PresentationsRelationManager;
use App\Models\Meeting;
use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
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
                        RichEditor::make('topics')
                            ->label('Gündem Maddeleri'),

                        RichEditor::make('decisions')
                            ->label('Kararlar'),
                    ]),

                Section::make('Katılımcılar')
                    ->schema([
                        Repeater::make('meetingUsers')
                            ->relationship('meetingUsers')
                            ->hiddenLabel()
                            ->schema([
                                Hidden::make('user_id')
                                    ->default(fn(Get $get) => $get('user_id')),

                                Checkbox::make('is_participated')
                                    ->label(function (Get $get, $record) {
                                        return $get('name') ?? $record->user->name;
                                    })
                                    ->inline()
                                    ->default(true)
                                    ->live(),

                                TextInput::make('reason_for_not_participating')
                                    ->hiddenLabel()
                                    ->placeholder('Katılmama sebebi')
                                    ->columnSpanFull()
                                    ->hidden(function (Get $get) {
                                        return $get('is_participated');
                                    }),
                            ])
                            ->reorderable(false)
                            ->deletable(false)
                            ->addable(false)
                            ->default(User::active()->get()->map(fn(User $user) => [
                                'name' => $user->name,
                                'user_id' => $user->id,
                                'is_participated' => true,
                            ])->toArray()),


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
                    ]),
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

                TextColumn::make('date')
                    ->label('Tarih')
                    ->date('d F Y')
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
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('export')
                    ->label('PDF\'e Aktar')
                    ->color('info')
                    ->icon('heroicon-o-document')
                    ->url(fn(Meeting $record) => route('meetings.export.pdf', $record), true),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Group::make([
                    \Filament\Infolists\Components\Section::make([
                        Group::make([
                            TextEntry::make('order')
                                ->size(TextEntrySize::Large)
                                ->weight(FontWeight::Black)
                                ->hiddenLabel()
                                ->formatStateUsing(fn(string $state, Meeting $record) => $state . '. ' . $record->title)
                                ->columnSpan(3),

                            TextEntry::make('date')
                                ->hiddenLabel()
                                ->columnSpan(1)
                                ->alignRight(),

                            TextEntry::make('location')
                                ->label('Mekân: ')
                                ->inlineLabel(),

                            TextEntry::make('abstainedUsers')
                                ->label('Katılmayanlar')
                                ->listWithLineBreaks()
                                ->bulleted()
                                ->formatStateUsing(fn($state) => $state->name . ' (' . $state->pivot->reason_for_not_participating . ')')
                                ->columnSpanFull(),

                            TextEntry::make('guests')
                                ->label('Misafirler')
                                ->listWithLineBreaks()
                                ->bulleted()
                                ->columnSpanFull()
                                ->formatStateUsing(fn(array $state) => implode(', ', $state))
                                ->visible(fn(Meeting $record) => $record->guests),

                            TextEntry::make('topics')
                                ->label('Gündem Maddeleri')
                                ->html()
                                ->columnSpanFull()
                                ->visible(fn(Meeting $record) => $record->topics),

                            TextEntry::make('decisions')
                                ->label('Kararlar')
                                ->html()
                                ->columnSpanFull()
                                ->visible(fn(Meeting $record) => $record->decisions),
                        ])
                            ->columns(4)
                    ]),
                ])
                    ->columnSpan(2),

                Group::make([
                    \Filament\Infolists\Components\Section::make([
                        ImageEntry::make('book.image')
                            ->hiddenLabel(),

                        TextEntry::make('book.name')
                            ->hiddenLabel(),

                        TextEntry::make('book.writer.name')
                            ->hiddenLabel(),

                        TextEntry::make('book.publisher.name')
                            ->hiddenLabel(),

                    ])
                        ->columnSpanFull()
                        ->heading('Kitap Bilgileri'),
                ])
                    ->columnSpan(1)
            ])
            ->columns(3);
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
            'view' => ViewMeeting::route('/{record}'),
            'edit' => EditMeeting::route('/{record}/edit'),
        ];
    }
}
