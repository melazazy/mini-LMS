<?php

namespace App\Filament\Resources\CompletedCourseResource\Pages;

use App\Filament\Resources\CompletedCourseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompletedCourses extends ListRecords
{
    protected static string $resource = CompletedCourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - this is a read-only resource
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Add statistics widget here if needed
        ];
    }
}
