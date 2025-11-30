<?php

namespace App\Filament\Resources\Books\Schemas;

use App\Models\Book;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make([
                    Section::make([
                        TextEntry::make('name')
                            ->label('Kitap')
                            ->columnSpan(3),

                        TextEntry::make('writer.name')
                            ->label('Yazar')
                            ->columnSpan(3),

                        TextEntry::make('publisher.name')
                            ->label('Yayınevi')
                            ->columnSpan(2),

                        TextEntry::make('page_count')
                            ->label('Sayfa Sayısı')
                            ->columnSpan(2),

                        TextEntry::make('reviews.rating')
                            ->label('Ortalama Puan')
                            ->formatStateUsing(fn (Book $record) => number_format($record->reviews()->whereNotNull('rating')->avg('rating'), 1, ','))
                            ->columnSpan(2),
                    ])
                        ->columns(6),
                ])
                    ->columnSpan(4),

                Group::make([
                    Section::make([
                        ImageEntry::make('image')
                            ->label('Kapak Resmi')
                            ->columnSpanFull(),
                    ]),
                ])
                    ->columnSpan(1),
            ])
            ->columns(5);
    }
}
