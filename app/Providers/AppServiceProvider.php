<?php

namespace App\Providers;

use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        CreateAction::configureUsing(function (CreateAction $action) {
            $action->icon('heroicon-o-plus');
        });

        ImportAction::configureUsing(function (ImportAction $action) {
            $action->icon('heroicon-o-document-arrow-up');
        });
    }
}
