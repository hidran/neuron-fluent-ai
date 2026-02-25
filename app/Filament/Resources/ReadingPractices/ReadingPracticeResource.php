<?php

namespace App\Filament\Resources\ReadingPractices;

use App\Filament\Resources\ReadingPractices\Pages\CreateReadingPractice;
use App\Filament\Resources\ReadingPractices\Pages\EditReadingPractice;
use App\Filament\Resources\ReadingPractices\Pages\ListReadingPractices;
use App\Filament\Resources\ReadingPractices\Schemas\ReadingPracticeForm;
use App\Filament\Resources\ReadingPractices\Tables\ReadingPracticesTable;
use App\Models\ReadingSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ReadingPracticeResource extends Resource
{
    protected static ?string $model = ReadingSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Reading Practices';

    protected static ?string $modelLabel = 'Reading Practice';

    protected static ?string $pluralModelLabel = 'Reading Practices';

    protected static UnitEnum|string|null $navigationGroup = 'Reading';

    public static function form(Schema $schema): Schema
    {
        return ReadingPracticeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReadingPracticesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'user',
                'readingCategory',
                'recordings',
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReadingPractices::route('/'),
            'create' => CreateReadingPractice::route('/create'),
            'edit' => EditReadingPractice::route('/{record}/edit'),
        ];
    }
}
