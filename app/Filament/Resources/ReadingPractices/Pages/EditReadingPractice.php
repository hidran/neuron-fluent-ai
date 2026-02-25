<?php

namespace App\Filament\Resources\ReadingPractices\Pages;

use App\Filament\Resources\ReadingPractices\ReadingPracticeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReadingPractice extends EditRecord
{
    protected static string $resource = ReadingPracticeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
