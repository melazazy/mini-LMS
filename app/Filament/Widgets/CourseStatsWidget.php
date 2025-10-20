<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CourseStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalCourses = Course::count();
        $publishedCourses = Course::where('is_published', true)->count();
        $freeCourses = Course::whereNull('price')->count();
        $paidCourses = Course::whereNotNull('price')->count();

        return [
            Stat::make('Total Courses', $totalCourses)
                ->description('All courses in the system')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),
            Stat::make('Published Courses', $publishedCourses)
                ->description('Currently available to students')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),
            Stat::make('Free Courses', $freeCourses)
                ->description('No cost courses')
                ->descriptionIcon('heroicon-m-gift')
                ->color('info'),
            Stat::make('Paid Courses', $paidCourses)
                ->description('Premium courses')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),
        ];
    }
}
