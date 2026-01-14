<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la categoría')
                    ->components([
                        Select::make('parent_id')
                            ->label('Categoría padre')
                            ->relationship(
                                name: 'parent',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query, ?Model $record) =>
                                $record ? $query->where('id', '!=', $record->id) : $query
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn(Model $record) =>
                                $record->full_name ?? $record->name
                            )
                            ->rules([
                                fn($record) => function ($attribute, $value, $fail) use ($record) {
                                    if ($record && $value) {
                                        $category = Category::find($value);
                                        if ($category && $category->isDescendantOf($record)) {
                                            $fail('La categoría seleccionada no puede ser una categoría padre de sí misma.');
                                        }
                                    }
                                },
                            ])
                            ->searchable()
                            ->preload()
                            ->live()
                            ->nullable(),

                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->live(onBlur: true)
                            ->maxLength(255),

                        Select::make('type')
                            ->label('Tipo')
                            ->required()
                            ->options([
                                'expense' => 'Gasto',
                                'income' => 'Ingreso',
                            ])
                            ->reactive(),

                        ColorPicker::make('color')
                            ->label('Color')
                            ->default('#3b82f6'),

                        TextInput::make('icon')
                            ->label('Icono')
                            ->placeholder('heroicon-o-home'),

                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ]),

                Section::make('Vista previa')
                    ->components([
                        Placeholder::make('tree_viz')
                            ->hiddenLabel()
                            ->content(function (Get $get, ?Model $record) {
                                $parentId = $get('parent_id');
                                $name = $get('name') ?: 'Nueva Categoría';
                                $color = $get('color') ?: '#cccccc';
                                $icon = $get('icon') ?: 'heroicon-o-folder';

                                $ancestors = collect([]);

                                if ($parentId) {
                                    $parent = \App\Models\Category::find($parentId);
                                    while ($parent) {
                                        $ancestors->prepend($parent);
                                        $parent = $parent->parent;
                                    }
                                }

                                $html = '<div class="flex flex-col gap-1">';

                                $depth = 0;
                                foreach ($ancestors as $ancestor) {
                                    $padding = $depth * 20;
                                    $html .= "
                                        <div class='flex items-center gap-2 text-gray-500' style='margin-left: {$padding}px'>
                                            <div class='w-6 h-6 flex items-center justify-center'>
                                                <x-icon name='heroicon-m-chevron-right' class='w-4 h-4'/>
                                            </div>
                                            <span class='text-sm'>{$ancestor->name}</span>
                                        </div>
                                        <div class='border-l-2 border-gray-300 h-2' style='margin-left: " . ($padding + 11) . "px'></div>
                                    ";
                                    $depth++;
                                }

                                $currentPadding = $depth * 20;
                                $iconHtml = '↳';

                                $html .= "
                                    <div class='flex items-center gap-3 p-2 rounded-lg border border-gray-200 bg-white shadow-sm ring-1 ring-gray-950/5' style='margin-left: {$currentPadding}px'>
                                        <div class='flex h-8 w-8 items-center justify-center rounded-full shadow-sm'>
                                            {$iconHtml}
                                        </div>
                                        <div class='flex flex-col'>
                                            <span class='font-bold text-gray-950'>{$name}</span>
                                        </div>
                                    </div>
                                ";

                                if ($record && $record->children()->exists()) {
                                    $childPadding = ($depth + 1) * 20;
                                    $html .= "<div class='border-l-2 border-gray-300 h-4' style='margin-left: " . ($currentPadding + 15) . "px'></div>";

                                    foreach ($record->children as $child) {
                                        $html .= "
                                            <div class='flex items-center gap-2 opacity-60' style='margin-left: {$childPadding}px'>
                                                <div class='border-b-2 border-gray-300 w-4 h-4 -mt-4 mr-1'></div>
                                                <span class='text-sm'>{$child->name}</span>
                                            </div>
                                        ";
                                    }
                                    if ($record->children()->count() > 3) {
                                        $html .= "<div class='text-xs text-gray-400 italic ml-[{$childPadding}px] pl-6'>... y más</div>";
                                    }
                                }

                                $html .= '</div>';

                                return new HtmlString($html);
                            }),
                    ]),
            ]);
    }
}
