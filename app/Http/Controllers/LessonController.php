<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function show(Lesson $lesson)
    {
        return view('lessons.show', compact('lesson'));
    }

    public function updateProgress(Lesson $lesson)
    {
        // Basic progress update - will be implemented later
        return response()->json(['success' => true]);
    }

    public function index()
    {
        $lessons = Lesson::all();
        return view('instructor.lessons.index', compact('lessons'));
    }

    public function create()
    {
        return view('instructor.lessons.create');
    }

    public function store(Request $request)
    {
        // Basic lesson creation - will be implemented later
        return redirect()->route('instructor.lessons.index');
    }

    public function edit(Lesson $lesson)
    {
        return view('instructor.lessons.edit', compact('lesson'));
    }

    public function update(Request $request, Lesson $lesson)
    {
        // Basic lesson update - will be implemented later
        return redirect()->route('instructor.lessons.index');
    }

    public function destroy(Lesson $lesson)
    {
        // Basic lesson deletion - will be implemented later
        return redirect()->route('instructor.lessons.index');
    }
}