<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use App\Filament\Resources\BookResource\RelationManagers\ReviewsRelationManager;
use Filament\Resources\Pages\ViewRecord;

class ViewBook extends ViewRecord
{
    protected static string $resource = BookResource::class;

    public function getRelationManagers(): array
    {
        return [
            ReviewsRelationManager::class,
        ];
    }
}
