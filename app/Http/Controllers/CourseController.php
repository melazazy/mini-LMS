<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        // Only show non-deleted courses by default
        $courses = Course::where('is_published', true)
            ->withCount(['enrollments', 'publishedLessons'])
            ->latest()
            ->paginate(12);
            
        return view('courses.index', compact('courses'));
    }

    public function show(Course $course)
    {
        // Check if course is deleted and user doesn't have permission to view
        if ($course->trashed() && !auth()->user()?->can('viewTrashed', $course)) {
            abort(404);
        }
        
        // Check if course is published and user is not admin/creator
        if (!$course->is_published && !auth()->user()?->can('viewUnpublished', $course)) {
            abort(404);
        }
        
        // Load relationships
        $course->loadCount(['enrollments', 'publishedLessons']);
        
        return view('courses.show', compact('course'));
    }
    
    public function watch(Course $course, ?Lesson $lesson = null)
    {
        // Ensure course is published
        if (!$course->is_published) {
            abort(404);
        }
        
        // If no lesson specified, get the first published lesson
        if (!$lesson) {
            $lesson = $course->publishedLessons()->orderBy('order')->first();
            
            if (!$lesson) {
                abort(404, 'No published lessons found for this course.');
            }
        }
        
        // Verify lesson belongs to this course
        if ($lesson->course_id !== $course->id) {
            abort(404, 'Lesson does not belong to this course.');
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
        // Show both active and trashed courses for the instructor
        $courses = Course::withTrashed()
            ->where('created_by', auth()->id())
            ->latest()
            ->get();
            
        return response()->json([
            'courses' => $courses, 
            'message' => 'Instructor courses accessed successfully'
        ]);
    }
    
    /**
     * Soft delete a course
     */
    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);
        
        $course->delete();
        
        return redirect()->route('instructor.courses')
            ->with('success', 'Course has been moved to trash.');
    }
    
    /**
     * Restore a soft-deleted course
     */
    public function restore($id)
    {
        $course = Course::withTrashed()->findOrFail($id);
        
        $this->authorize('restore', $course);
        
        $course->restore();
        
        return redirect()->route('instructor.courses')
            ->with('success', 'Course has been restored successfully.');
    }
    
    /**
     * Permanently delete a course
     */
    public function forceDelete($id)
    {
        $course = Course::withTrashed()->findOrFail($id);
        
        $this->authorize('forceDelete', $course);
        
        $course->forceDelete();
        
        return redirect()->route('instructor.courses')
            ->with('success', 'Course has been permanently deleted.');
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
}