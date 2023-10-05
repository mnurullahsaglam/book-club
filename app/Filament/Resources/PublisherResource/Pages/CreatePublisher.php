<?php

namespace App\Filament\Resources\PublisherResource\Pages;

use App\Filament\Resources\PublisherResource;
use App\Traits\FilamentRedirect;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePublisher extends CreateRecord
{
    use FilamentRedirect;

    protected static string $resource = PublisherResource::class;
}
