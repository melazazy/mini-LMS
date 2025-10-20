<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        return view('courses.index');
    }

    public function show(Course $course)
    {
        // Ensure course is published or user is the creator/admin
        if (!$course->is_published && !auth()->user()?->isAdmin() && $course->created_by !== auth()->id()) {
            abort(404);
        }
        
        return view('courses.show', compact('course'));
    }
    
    public function watch(Course $course, $lessonId = null)
    {
        // Ensure course is published
        if (!$course->is_published) {
            abort(404);
        }
        
        $lesson = null;
        if ($lessonId) {
            $lesson = $course->lessons()->findOrFail($lessonId);
        }
        
        return view('courses.watch', compact('course', 'lesson'));
    }

    public function enroll(Course $course)
    {
        // Basic enrollment logic - will be implemented later
        return redirect()->back()->with('success', 'Successfully enrolled in course!');
    }

    public function instructorIndex()
    {
        $courses = Course::where('created_by', auth()->id())->get();
        return response()->json(['courses' => $courses, 'message' => 'Instructor courses accessed successfully']);
    }

    public function create()
    {
        return view('instructor.courses.create');
    }

    public function store(Request $request)
    {
        // Basic course creation - will be implemented later
        return redirect()->route('instructor.courses.index');
    }

    public function edit(Course $course)
    {
        return view('instructor.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        // Basic course update - will be implemented later
        return redirect()->route('instructor.courses.index');
    }

    public function destroy(Course $course)
    {
        // Basic course deletion - will be implemented later
        return redirect()->route('instructor.courses.index');
    }
}