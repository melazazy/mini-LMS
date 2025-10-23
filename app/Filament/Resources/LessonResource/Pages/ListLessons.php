<?php

namespace App\Filament\Resources\LessonResource\Pages;

use App\Filament\Resources\LessonResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLessons extends ListRecords
{
    protected static string $resource = LessonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reorder')
                ->label('Reorder Lessons')
                ->icon('heroicon-o-arrows-up-down')
                ->url(route('filament.admin.resources.lessons.reorder'))
                ->color('gray'),
            Actions\CreateAction::make(),
        ];
    }
}
