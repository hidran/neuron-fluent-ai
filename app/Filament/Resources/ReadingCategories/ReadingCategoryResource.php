<?php

namespace App\Filament\Resources\ReadingCategories;

use App\Filament\Resources\ReadingCategories\Pages\CreateReadingCategory;
use App\Filament\Resources\ReadingCategories\Pages\EditReadingCategory;
use App\Filament\Resources\ReadingCategories\Pages\ListReadingCategories;
use App\Filament\Resources\ReadingCategories\Schemas\ReadingCategoryForm;
use App\Filament\Resources\ReadingCategories\Tables\ReadingCategoriesTable;
use App\Models\ReadingCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReadingCategoryResource extends Resource
{
    protected static ?string $model = ReadingCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ReadingCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReadingCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReadingCategories::route('/'),
            'create' => CreateReadingCategory::route('/create'),
            'edit' => EditReadingCategory::route('/{record}/edit'),
        ];
    }
}
