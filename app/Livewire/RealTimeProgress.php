<?php

namespace App\Livewire;

use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Real-time progress component that displays course progress
 * and updates via WebSocket broadcasts.
 */
class RealTimeProgress extends Component
{
    public Course $course;
    public array $progress = [];
    public bool $isConnected = false;

    /**
     * Mount the component with a course.
     */
    public function mount(Course $course): void
    {
        $this->course = $course;
        $this->loadProgress();
    }

    /**
     * Handle progress update from WebSocket broadcast.
     */
    #[On('echo-private:user.{userId},progress.updated')]
    public function handleProgressUpdate(array $data): void
    {
        if ($data['course_id'] === $this->course->id) {
            $this->loadProgress();
            $this->dispatch('progressUpdated');
        }
    }

    /**
     * Load progress data for all lessons in the course.
     */
    private function loadProgress(): void
    {
        if (!Auth::check()) {
            $this->progress = [];
            return;
        }

        $user = Auth::user();
        $lessons = $this->course->publishedLessons;
        $progressData = $user->lessonProgress()
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->get()
            ->keyBy('lesson_id');

        $this->progress = $lessons->map(function ($lesson) use ($progressData) {
            $progress = $progressData->get($lesson->id);
            return [
                'lesson_id' => $lesson->id,
                'percentage' => $progress ? $progress->watched_percentage : 0,
                'is_completed' => $progress ? $progress->isCompleted() : false,
                'is_in_progress' => $progress ? $progress->isInProgress() : false,
            ];
        })->toArray();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.real-time-progress');
    }
}
