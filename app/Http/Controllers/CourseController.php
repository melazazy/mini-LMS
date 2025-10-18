<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::where('is_published', true)->get();
        return view('courses.index', compact('courses'));
    }

    public function show(Course $course)
    {
        return view('courses.show', compact('course'));
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