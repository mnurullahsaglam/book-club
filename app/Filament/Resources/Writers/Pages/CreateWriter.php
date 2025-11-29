<?php

namespace App\Filament\Resources\Writers\Pages;

use App\Filament\Resources\Writers\WriterResource;
use App\Traits\FilamentRedirect;
use Filament\Resources\Pages\CreateRecord;

class CreateWriter extends CreateRecord
{
    use FilamentRedirect;

    protected static string $resource = WriterResource::class;
}
