<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Ensure user is an instructor or admin
        if (!Auth::user()->isInstructor() && !Auth::user()->isAdmin()) {
            abort(403, 'Access denied. Instructor privileges required.');
        }

        $user = Auth::user();

        // Get instructor's courses
        $courses = Course::where('created_by', $user->id)
            ->withCount(['lessons', 'enrollments'])
            ->latest()
            ->get();

        // Statistics
        $stats = [
            'total_courses' => $courses->count(),
            'published_courses' => $courses->where('is_published', true)->count(),
            'total_lessons' => Lesson::whereIn('course_id', $courses->pluck('id'))->count(),
            'total_students' => Enrollment::whereIn('course_id', $courses->pluck('id'))
                ->distinct('user_id')
                ->count('user_id'),
            'total_enrollments' => Enrollment::whereIn('course_id', $courses->pluck('id'))->count(),
        ];

        // Recent enrollments
        $recentEnrollments = Enrollment::whereIn('course_id', $courses->pluck('id'))
            ->with(['user', 'course'])
            ->latest()
            ->take(10)
            ->get();

        return view('dashboards.instructor', compact('courses', 'stats', 'recentEnrollments'));
    }
}
