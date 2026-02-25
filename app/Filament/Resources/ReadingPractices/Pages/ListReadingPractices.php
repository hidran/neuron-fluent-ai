<?php

namespace App\Filament\Resources\ReadingPractices\Pages;

use App\Filament\Resources\ReadingPractices\ReadingPracticeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReadingPractices extends ListRecords
{
    protected static string $resource = ReadingPracticeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
