<?php

namespace App\Filament\Resources\ReadingPractices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReadingPracticeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('reading_category_id')
                    ->relationship('readingCategory', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('language')
                    ->required()
                    ->options([
                        'en' => 'English',
                        'es' => 'Spanish',
                        'fr' => 'French',
                        'de' => 'German',
                        'it' => 'Italian',
                        'pt' => 'Portuguese',
                    ]),
                TextInput::make('ai_voice')
                    ->maxLength(255),
                Textarea::make('generated_text')
                    ->required()
                    ->rows(8)
                    ->columnSpanFull(),
                TextInput::make('audio_file_path')
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('pronunciation_score')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100),
                TextInput::make('intonation_score')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100),
                TextInput::make('grammar_score')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100),
                Textarea::make('ai_feedback')
                    ->rows(6)
                    ->columnSpanFull(),
            ]);
    }
}
