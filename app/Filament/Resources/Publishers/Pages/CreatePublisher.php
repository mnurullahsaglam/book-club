<?php

namespace App\Filament\Resources\Publishers\Pages;

use App\Filament\Resources\Publishers\PublisherResource;
use App\Traits\FilamentRedirect;
use Filament\Resources\Pages\CreateRecord;

class CreatePublisher extends CreateRecord
{
    use FilamentRedirect;

    protected static string $resource = PublisherResource::class;
}
