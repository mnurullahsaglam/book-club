<?php

namespace App\Filament\Resources\Meetings\Schemas;

use App\Models\Book;
use App\Models\Meeting;
use App\Models\User;
use App\Models\Writer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Database\Query\Builder;

class MeetingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                MorphToSelect::make('meetable')
                    ->label('Kitap/Yazar')
                    ->types([
                        Type::make(Book::class)
                            ->titleAttribute('name')
                            ->label('Kitap'),
                        Type::make(Writer::class)
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
                    ->afterStateUpdated(function (Set $set, Get $get, ?array $state, ?Meeting $record) {
                        if (! is_array($state) || ! isset($state['meetable_id'])) {
                            $set('order', 1);

                            return;
                        }

                        $newWriterId = self::resolveWriterId($state['meetable_type'], $state['meetable_id']);

                        if ($record) {
                            $oldWriterId = self::resolveWriterId($record->meetable_type, $record->meetable_id);

                            if ($oldWriterId === $newWriterId) {
                                $set('order', $record->order);

                                return;
                            }
                        }

                        $date = $get('date') ?? now()->toDateString();

                        $set('order', self::calculateOrder($newWriterId, $date, $record));
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
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?Meeting $record) {
                        if (! $state) {
                            return;
                        }

                        $meetable = $get('meetable');

                        if (is_array($meetable) && isset($meetable['meetable_id'])) {
                            $writerId = self::resolveWriterId($meetable['meetable_type'], $meetable['meetable_id']);
                        } elseif ($record) {
                            $writerId = self::resolveWriterId($record->meetable_type, $record->meetable_id);
                        } else {
                            return;
                        }

                        $set('order', self::calculateOrder($writerId, $state, $record));
                    }),

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
                            ->relationship('meetingUsers', fn (Builder $query) => $query->with('user'))
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

    private static function resolveWriterId(string $meetableType, int|string $meetableId): int
    {
        if ($meetableType === Book::class) {
            return Book::find($meetableId)->writer_id;
        }

        return (int) $meetableId;
    }

    private static function calculateOrder(int $writerId, string $date, ?Meeting $record = null): int
    {
        return Meeting::whereHasMorph(
            'meetable',
            [Book::class, Writer::class],
            function (Builder $query, string $type) use ($writerId) {
                $column = $type === Book::class ? 'writer_id' : 'id';
                $query->where($column, $writerId);
            }
        )
            ->when($record, fn (Builder $query) => $query->where('id', '!=', $record->id))
            ->where('date', '<=', $date)
            ->count() + 1;
    }
}
