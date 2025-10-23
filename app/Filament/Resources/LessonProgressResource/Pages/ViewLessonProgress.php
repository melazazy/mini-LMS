<?php

namespace App\Filament\Resources\LessonProgressResource\Pages;

use App\Filament\Resources\LessonProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLessonProgress extends ViewRecord
{
    protected static string $resource = LessonProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
