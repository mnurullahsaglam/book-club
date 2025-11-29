<?php

namespace App\Filament\Resources\Books\Pages;

use App\Filament\Resources\Books\BookResource;
use App\Traits\FilamentRedirect;
use Filament\Resources\Pages\CreateRecord;

class CreateBook extends CreateRecord
{
    use FilamentRedirect;

    protected static string $resource = BookResource::class;
}
