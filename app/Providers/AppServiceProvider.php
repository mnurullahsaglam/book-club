<?php

namespace App\Providers;

use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Illuminate\Database\Eloquent\Model;
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
        Model::shouldBeStrict(! app()->isProduction());

        CreateAction::configureUsing(function (CreateAction $action) {
            $action->icon('heroicon-o-plus');
        });

        ImportAction::configureUsing(function (ImportAction $action) {
            $action
                ->color('info')
                ->icon('heroicon-o-document-arrow-up');
        });

        ExportAction::configureUsing(function (ExportAction $action) {
            $action
                ->color('primary')
                ->icon('heroicon-o-document-arrow-down');
        });

        ExportBulkAction::configureUsing(function (ExportBulkAction $action) {
            $action
                ->color('primary')
                ->icon('heroicon-o-document-arrow-down');
        });
    }
}
