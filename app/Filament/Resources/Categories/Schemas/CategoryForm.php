<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la categoría')
                    ->components([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required(),

                        Select::make('type')
                            ->label('Tipo')
                            ->options(['expense' => 'Gasto', 'income' => 'Ingreso'])
                            ->required(),

                        ColorPicker::make('color')
                            ->label('Color')
                            ->required()
                            ->default('#3b82f6'),

                        Select::make('icon')
                            ->label('Icono')
                            ->allowHtml()
                            ->options([
                                'heroicon-o-home' => new HtmlString('<div class="flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">...</svg> Inicio</div>'),
                                'heroicon-o-user' => new HtmlString('<div class="flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">...</svg> Usuario</div>'),
                                'heroicon-o-user-group' => new HtmlString('<div class="flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">...</svg> Grupo</div>'),
                                'heroicon-o-cog' => new HtmlString('<div class="flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">...</svg> Configuración</div>'),
                                'heroicon-o-envelope' => new HtmlString('<div class="flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">...</svg> Correo</div>'),
                            ])
                            ->preload(),
                    ]),
                
                Section::make('Estado')
                    ->components([
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->required(),
                    ]),
            ]);
    }
}
