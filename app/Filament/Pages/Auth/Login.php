<?php

namespace App\Filament\Pages\Auth;

class Login extends \Filament\Auth\Pages\Login
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public function mount(): void
    {
        parent::mount();

        if (app()->environment('local')) {
            $this->form->fill([
                'email' => 'nurullahsl87@gmail.com',
                'password' => 'password',
                'remember' => true,
            ]);
        }
    }
}
