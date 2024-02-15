<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class VersionNotes extends Widget
{
    protected static string $view = 'filament.widgets.version-notes';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 2;

    public string $label = 'Versiyon Notları';
}
