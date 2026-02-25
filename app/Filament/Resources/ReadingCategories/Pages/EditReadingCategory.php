<?php

namespace App\Filament\Resources\ReadingCategories\Pages;

use App\Filament\Resources\ReadingCategories\ReadingCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReadingCategory extends EditRecord
{
    protected static string $resource = ReadingCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
