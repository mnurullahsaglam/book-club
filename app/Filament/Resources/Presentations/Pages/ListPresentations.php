<?php

namespace App\Filament\Resources\Presentations\Pages;

use Filament\Schemas\Components\Tabs\Tab;
use App\Filament\Resources\Presentations\PresentationResource;
use App\Models\Presentation;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPresentations extends ListRecords
{
    protected static string $resource = PresentationResource::class;

    public function getTabs(): array
    {
        return [
            'owner' => Tab::make()
                ->badge(Presentation::owner()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->owner())
                ->label('Sunumlarım'),
            'all' => Tab::make()
                ->badge(Presentation::count())
                ->label('Tüm Sunumlar'),
        ];
    }
}
