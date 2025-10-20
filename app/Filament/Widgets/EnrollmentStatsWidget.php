<?php

namespace App\Filament\Widgets;

use App\Models\Enrollment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EnrollmentStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalEnrollments = Enrollment::count();
        $activeEnrollments = Enrollment::where('status', 'active')->count();
        $freeEnrollments = Enrollment::whereNull('paid_amount')->count();
        $paidEnrollments = Enrollment::whereNotNull('paid_amount')->count();

        return [
            Stat::make('Total Enrollments', $totalEnrollments)
                ->description('All time enrollments')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary'),
            Stat::make('Active Enrollments', $activeEnrollments)
                ->description('Currently active')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Free Enrollments', $freeEnrollments)
                ->description('No cost enrollments')
                ->descriptionIcon('heroicon-m-gift')
                ->color('info'),
            Stat::make('Paid Enrollments', $paidEnrollments)
                ->description('Revenue generating')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),
        ];
    }
}
