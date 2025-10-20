<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\CourseCompletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Ensure user is a student
        if (!Auth::user()->isStudent()) {
            abort(403, 'Access denied. Student privileges required.');
        }

        $user = Auth::user();

        // Get student's enrollments with progress
        $enrollments = Enrollment::where('user_id', $user->id)
            ->where('status', 'active')
            ->with(['course.lessons'])
            ->latest()
            ->get()
            ->map(function ($enrollment) use ($user) {
                $course = $enrollment->course;
                $totalLessons = $course->lessons->count();
                
                // Get completed lessons count (90% or more watched)
                $completedLessons = LessonProgress::where('user_id', $user->id)
                    ->whereIn('lesson_id', $course->lessons->pluck('id'))
                    ->where('watched_percentage', '>=', 90)
                    ->count();
                
                // Calculate progress percentage
                $progressPercentage = $totalLessons > 0 
                    ? round(($completedLessons / $totalLessons) * 100) 
                    : 0;
                
                $enrollment->progress_percentage = $progressPercentage;
                $enrollment->completed_lessons = $completedLessons;
                $enrollment->total_lessons = $totalLessons;
                
                return $enrollment;
            });

        // Statistics
        $stats = [
            'active_courses' => $enrollments->count(),
            'completed_courses' => CourseCompletion::where('user_id', $user->id)->count(),
            'total_lessons_completed' => LessonProgress::where('user_id', $user->id)
                ->where('watched_percentage', '>=', 90)
                ->count(),
            'total_watch_time' => LessonProgress::where('user_id', $user->id)
                ->sum('last_position_seconds'),
        ];

        // Continue watching (courses with progress but not completed)
        $continueWatching = $enrollments->filter(function ($enrollment) {
            return $enrollment->progress_percentage > 0 && $enrollment->progress_percentage < 100;
        })->take(5);

        // Recently completed courses
        $completedCourses = CourseCompletion::where('user_id', $user->id)
            ->with('course')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboards.student', compact('enrollments', 'stats', 'continueWatching', 'completedCourses'));
    }
}
