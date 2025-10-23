<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class OverviewStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Course stats
        $totalCourses = Course::count();
        $publishedCourses = Course::where('is_published', true)->count();
        
        // Enrollment stats
        $totalEnrollments = Enrollment::count();
        $activeEnrollments = Enrollment::where('status', 'active')->count();
        
        // Calculate average completion percentage
        $avgCompletion = $this->calculateAverageCompletion();
        
        // Revenue stats
        $totalRevenue = Enrollment::whereNotNull('paid_amount')
            ->where('status', 'active')
            ->sum('paid_amount');

        return [
            Stat::make('Total Courses', $totalCourses)
                ->description($publishedCourses . ' published')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary')
                ->chart($this->getCourseTrend()),
                
            Stat::make('Total Enrollments', $totalEnrollments)
                ->description($activeEnrollments . ' active')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success')
                ->chart($this->getEnrollmentTrend()),
                
            Stat::make('Avg Completion', number_format($avgCompletion, 1) . '%')
                ->description('Across all active enrollments')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($this->getCompletionColor($avgCompletion))
                ->chart($this->getCompletionTrend()),
                
            Stat::make('Total Revenue', '$' . number_format($totalRevenue, 2))
                ->description('From paid enrollments')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),
        ];
    }

    /**
     * Calculate average completion percentage across all active enrollments
     */
    protected function calculateAverageCompletion(): float
    {
        $activeEnrollments = Enrollment::where('status', 'active')
            ->with(['course.lessons', 'user'])
            ->get();

        if ($activeEnrollments->isEmpty()) {
            return 0;
        }

        $totalCompletion = 0;
        $validEnrollments = 0;

        foreach ($activeEnrollments as $enrollment) {
            $completionPercentage = $enrollment->getCompletionPercentage();
            $totalCompletion += $completionPercentage;
            $validEnrollments++;
        }

        return $validEnrollments > 0 ? $totalCompletion / $validEnrollments : 0;
    }

    /**
     * Get color based on completion percentage
     */
    protected function getCompletionColor(float $percentage): string
    {
        return match (true) {
            $percentage >= 70 => 'success',
            $percentage >= 40 => 'warning',
            default => 'danger',
        };
    }

    /**
     * Get course creation trend for last 7 days
     */
    protected function getCourseTrend(): array
    {
        $trend = Course::where('created_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();

        return array_pad($trend, 7, 0);
    }

    /**
     * Get enrollment trend for last 7 days
     */
    protected function getEnrollmentTrend(): array
    {
        $trend = Enrollment::where('created_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();

        return array_pad($trend, 7, 0);
    }

    /**
     * Get completion trend (simplified - shows completion distribution)
     */
    protected function getCompletionTrend(): array
    {
        // Get completion percentages for last 7 days
        $completions = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $enrollments = Enrollment::where('status', 'active')
                ->where('created_at', '<=', $date)
                ->get();
            
            if ($enrollments->isEmpty()) {
                $completions[] = 0;
                continue;
            }

            $total = 0;
            foreach ($enrollments as $enrollment) {
                $total += $enrollment->getCompletionPercentage();
            }
            
            $completions[] = $enrollments->count() > 0 ? $total / $enrollments->count() : 0;
        }

        return $completions;
    }
}
