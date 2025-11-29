<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('İsim')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('E-posta Adresi')
                    ->email()
                    ->required()
                    ->maxLength(255),

                TextInput::make('password')
                    ->label('Parola')
                    ->password()
                    ->required()
                    ->maxLength(255)
                    ->hiddenOn('edit'),

                DatePicker::make('registered_at')
                    ->label('Kayıt Tarihi'),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }
}

