<?php

namespace App\Filament\Resources\WriterResource\Pages;

use App\Filament\Resources\WriterResource;
use App\Traits\FilamentRedirect;
use Filament\Resources\Pages\CreateRecord;

class CreateWriter extends CreateRecord
{
    use FilamentRedirect;

    protected static string $resource = WriterResource::class;
}
