<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información básica')
                    ->components([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('password')
                            ->password()
                            ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                            ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                            ->hiddenOn('edit'),
                    ])
                    ->columns(2),

                Section::make('Configuración')
                    ->components([
                        Select::make('timezone')
                            ->options([
                                'America/Bogota' => 'Bogotá',
                                'America/Mexico_City' => 'México',
                                'America/New_York' => 'New York',
                            ])
                            ->required(),

                        Select::make('locale')
                            ->options([
                                'es' => 'Español',
                                'en' => 'English',
                            ])
                            ->required(),

                        Select::make('currency_default')
                            ->options([
                                'COP' => 'COP',
                                'USD' => 'USD',
                            ])
                            ->required(),

                        Toggle::make('is_active')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('Roles')
                    ->components([
                        Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload(),
                    ]),
            ]);
    }
}
