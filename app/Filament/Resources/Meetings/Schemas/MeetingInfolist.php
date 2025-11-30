<?php

namespace App\Filament\Resources\Meetings\Schemas;

use App\Models\Book;
use App\Models\Meeting;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class MeetingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make([
                    Section::make([
                        Group::make([
                            TextEntry::make('order')
                                ->size(TextSize::Large)
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
                        ])
                            ->columns(4),
                    ])
                        ->heading('Toplantı Bilgileri'),
                ])
                    ->columnSpan(2),

                Group::make([
                    Section::make([
                        ImageEntry::make('meetable.image')
                            ->hiddenLabel(),

                        TextEntry::make('meetable.name')
                            ->hiddenLabel(),

                        TextEntry::make('meetable.writer.name')
                            ->visible(fn ($record) => $record->meetable_type === Book::class)
                            ->hiddenLabel(),

                        TextEntry::make('meetable.publisher.name')
                            ->visible(fn ($record) => $record->meetable_type === Book::class)
                            ->hiddenLabel(),

                    ])
                        ->columnSpanFull()
                        ->heading('Kitap/Yazar Bilgileri'),
                ])
                    ->columnSpan(1),
            ])
            ->columns(3);
    }
}
