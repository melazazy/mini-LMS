<?php

namespace App\Filament\Resources\LessonResource\Pages;

use App\Filament\Resources\LessonResource;
use App\Models\Lesson;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class ReorderLessons extends Page
{
    protected static string $resource = LessonResource::class;

    protected static string $view = 'filament.resources.lesson-resource.pages.reorder-lessons';

    protected static ?string $title = 'Reorder Lessons';

    protected static ?string $navigationIcon = 'heroicon-o-arrows-up-down';

    public $lessons = [];
    public $selectedCourse = null;
    public $courses = [];

    public function mount(): void
    {
        $this->courses = \App\Models\Course::orderBy('title')->pluck('title', 'id')->toArray();
        $this->loadLessons();
    }

    public function loadLessons(): void
    {
        $query = Lesson::with('course')->orderBy('order');
        
        if ($this->selectedCourse) {
            $query->where('course_id', $this->selectedCourse);
        }
        
        $this->lessons = $query->get()->map(function ($lesson) {
            return [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'course' => $lesson->course->title,
                'order' => $lesson->order,
                'duration' => gmdate('H:i:s', $lesson->duration_seconds),
                'is_published' => $lesson->is_published,
            ];
        })->toArray();
    }

    public function updateOrder($orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                Lesson::where('id', $id)->update(['order' => $index + 1]);
            }
        });

        $this->loadLessons();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Lesson order updated successfully!',
        ]);
    }

    public function updatedSelectedCourse(): void
    {
        $this->loadLessons();
    }
}
