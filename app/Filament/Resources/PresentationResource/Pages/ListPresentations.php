<?php

namespace App\Filament\Resources\PresentationResource\Pages;

use App\Filament\Resources\PresentationResource;
use App\Models\Presentation;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPresentations extends ListRecords
{
    protected static string $resource = PresentationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'owner' => Tab::make()
                ->badge(Presentation::owner()->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->owner())
                ->label('Sunumlarım'),
            'all' => Tab::make()
                ->badge(Presentation::count())
                ->label('Tüm Sunumlar'),
        ];
    }
}
