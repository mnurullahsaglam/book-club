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
                    ->afterStateUpdated(function (Set $set, ?array $state, ?Meeting $record) {
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
                                ->when($record, function (Builder $query) use ($record) {
                                    $query->where('id', '!=', $record->id);
                                })
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
}

