<?php

namespace App\Filament\Resources\ReadingCategories\Pages;

use App\Filament\Resources\ReadingCategories\ReadingCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReadingCategory extends CreateRecord
{
    protected static string $resource = ReadingCategoryResource::class;
}
