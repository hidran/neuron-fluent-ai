<?php

namespace App\Filament\Resources\ReadingCategories\Pages;

use App\Filament\Resources\ReadingCategories\ReadingCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReadingCategories extends ListRecords
{
    protected static string $resource = ReadingCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
