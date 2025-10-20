<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalUsers = User::count();
        $students = User::where('role', 'student')->count();
        $instructors = User::where('role', 'instructor')->count();
        $admins = User::where('role', 'admin')->count();

        return [
            Stat::make('Total Users', $totalUsers)
                ->description('All registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Students', $students)
                ->description('Learning users')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),
            Stat::make('Instructors', $instructors)
                ->description('Content creators')
                ->descriptionIcon('heroicon-m-user')
                ->color('info'),
            Stat::make('Admins', $admins)
                ->description('System administrators')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('warning'),
        ];
    }
}
