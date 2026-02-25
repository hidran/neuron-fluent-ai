<?php

namespace App\Filament\Resources\ReadingCategories\Schemas;

use Filament\Schemas\Schema;

class ReadingCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->maxLength(65535),
                \Filament\Forms\Components\Select::make('difficulty_level')
                    ->required()
                    ->options([
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced' => 'Advanced',
                    ])
                    ->default('beginner'),
                \Filament\Forms\Components\Toggle::make('is_active')
                    ->default(true),
                \Filament\Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
