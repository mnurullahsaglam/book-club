<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeetingResource\Pages\CreateMeeting;
use App\Filament\Resources\MeetingResource\Pages\EditMeeting;
use App\Filament\Resources\MeetingResource\Pages\ListMeetings;
use App\Filament\Resources\MeetingResource\Pages\ViewMeeting;
use App\Filament\Resources\MeetingResource\RelationManagers\PresentationsRelationManager;
use App\Models\Book;
use App\Models\Meeting;
use App\Models\User;
use App\Models\Writer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
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
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Query\Builder;

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
                MorphToSelect::make('meetable')
                    ->label('Kitap/Yazar')
                    ->types([
                        MorphToSelect\Type::make(Book::class)
                            ->titleAttribute('name')
                            ->label('Kitap'),
                        MorphToSelect\Type::make(Writer::class)
                            ->titleAttribute('name')
                            ->label('Yazar'),
                    ])
                    ->extraAttributes([
                        'class' => 'border-none px-0 py-1',
                    ])
                    ->columns()
                    ->columnSpanFull()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?array $state) {
                        if (is_array($state) && isset($state['meetable_id'])) {
                            $writerId = $state['meetable_id'];

                            if ($state['meetable_type'] === Book::class) {
                                $writerId = Book::find($state['meetable_id'])->writer_id;
                            }

                            $meetingCount = Meeting::whereHasMorph(
                                'meetable',
                                [Book::class, Writer::class],
                                function (Builder $query, string $type) use ($writerId) {
                                    $column = $type === Book::class ? 'writer_id' : 'id';

                                    $query->where($column, $writerId);
                                }
                            )
                                ->count();

                            $set('order', $meetingCount + 1);
                        } else {
                            $set('order', 1);
                        }
                    })
                    ->required(),

                TextInput::make('title')
                    ->label('Başlık')
                    ->required()
                    ->maxLength(255),

                TextInput::make('order')
                    ->label('Sıra')
                    ->required()
                    ->numeric()
                    ->minValue(1),

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
                                    ->default(fn (Get $get) => $get('user_id')),

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
                            ->default(User::active()->get()->map(fn (User $user) => [
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
                            ->defaultItems(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable(),

                TextColumn::make('date')
                    ->label('Tarih')
                    ->date('d F Y')
                    ->sortable(),

                TextColumn::make('order')
                    ->label('Sıra')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('meetable.name')
                    ->label('Kitap/Yazar')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from')
                            ->label('Tarihinden'),
                        DatePicker::make('to')
                            ->label('Tarihine'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['from'], function ($query) use ($data) {
                            $query->whereDate('date', '>=', $data['from']);
                        })->when($data['to'], function ($query) use ($data) {
                            $query->whereDate('date', '<=', $data['to']);
                        });
                    })
                    ->indicateUsing(function (array $data) {
                        if (! $data['from'] && ! $data['to']) {
                            return null;
                        }

                        return 'Tarih Aralığı: '.($data['from'] ?? '...').' - '.($data['to'] ?? '...');
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->color('warning'),
                    DeleteAction::make(),
                    Action::make('export')
                        ->label('PDF\'e Aktar')
                        ->color('info')
                        ->icon('heroicon-o-document')
                        ->url(fn (Meeting $record) => route('meetings.export.pdf', $record), true),
                ]),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
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
                                ->formatStateUsing(fn (string $state, Meeting $record) => $state.'. '.$record->title)
                                ->columnSpan(3),

                            TextEntry::make('date')
                                ->formatStateUsing(fn ($state) => $state->format('d/m/Y'))
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
                                ->formatStateUsing(fn ($state) => $state->name.' ('.$state->pivot->reason_for_not_participating.')')
                                ->columnSpanFull(),

                            TextEntry::make('guests')
                                ->label('Misafirler')
                                ->listWithLineBreaks()
                                ->bulleted()
                                ->columnSpanFull()
                                ->formatStateUsing(fn (array $state) => implode(', ', $state))
                                ->visible(fn (Meeting $record) => $record->guests),

                            TextEntry::make('topics')
                                ->label('Gündem Maddeleri')
                                ->html()
                                ->columnSpanFull()
                                ->visible(fn (Meeting $record) => $record->topics),

                            TextEntry::make('decisions')
                                ->label('Kararlar')
                                ->html()
                                ->columnSpanFull()
                                ->visible(fn (Meeting $record) => $record->decisions),

                            TextEntry::make('presentations')
                                ->label('Sunumlar')
                                ->html()
                                ->columnSpanFull()
                                ->visible(fn (Meeting $record) => $record->decisions),
                        ])
                            ->columns(4),
                    ])
                    ->heading('Toplantı Bilgileri'),
                ])
                    ->columnSpan(2),

                Group::make([
                    \Filament\Infolists\Components\Section::make([
                        ImageEntry::make('meetable.image')
                            ->hiddenLabel(),

                        TextEntry::make('meetable.name')
                            ->hiddenLabel(),

                        TextEntry::make('meetable.writer.name')
                            ->visible(fn($record) => $record->meetable_type === Book::class)
                            ->hiddenLabel(),

                        TextEntry::make('meetable.publisher.name')
                            ->visible(fn($record) => $record->meetable_type === Book::class)
                            ->hiddenLabel(),

                    ])
                        ->columnSpanFull()
                        ->heading('Kitap/Yazar Bilgileri'),
                ])
                    ->columnSpan(1),
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
