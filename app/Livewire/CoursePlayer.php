<?php

namespace App\Livewire;

use App\Actions\Progress\UpdateLessonProgressAction;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CoursePlayer extends Component
{
    public Course $course;
    public Lesson $currentLesson;
    public $progress = [];
    public $isEnrolled = false;
    public $canWatchLesson = false;
    public $lessonsProgress = [];

    protected $listeners = ['lessonSelected' => 'loadLesson'];

    public function mount(Course $course, Lesson $lesson = null)
    {
        $this->course = $course->load('publishedLessons');
        $this->currentLesson = $lesson ?? $course->publishedLessons()->first();
        
        if (!$this->currentLesson) {
            abort(404, 'No published lessons found for this course.');
        }
        
        // Ensure course relationship is loaded
        if (!$this->currentLesson->relationLoaded('course')) {
            $this->currentLesson->load('course');
        }
        
        $this->checkEnrollment();
        $this->loadAllLessonsProgress();
        $this->checkLessonAccess();
        $this->loadProgress();
    }

    public function loadLesson($lessonId)
    {
        $this->currentLesson = Lesson::with('course')->findOrFail($lessonId);
        $this->checkLessonAccess();
        $this->loadProgress();
        $this->dispatch('lessonChanged');
    }

    public function updateProgress($percentage, $position)
    {
        if (!Auth::check() || !$this->isEnrolled) {
            return;
        }

        // Validate inputs
        if ($percentage === null || $position === null) {
            return;
        }

        // Ensure values are integers
        $percentage = (int) $percentage;
        $position = (int) $position;

        // Validate percentage range
        if ($percentage < 0 || $percentage > 100) {
            return;
        }

        $action = app(UpdateLessonProgressAction::class);
        $action->execute(Auth::user(), $this->currentLesson, $percentage, $position);
        
        $this->loadProgress();
        $this->dispatch('progressUpdated');
    }

    public function nextLesson()
    {
        $nextLesson = $this->course->publishedLessons()
            ->where('order', '>', $this->currentLesson->order)
            ->first();

        if ($nextLesson) {
            $this->loadLesson($nextLesson->id);
        }
    }

    public function previousLesson()
    {
        $previousLesson = $this->course->publishedLessons()
            ->where('order', '<', $this->currentLesson->order)
            ->orderBy('order', 'desc')
            ->first();

        if ($previousLesson) {
            $this->loadLesson($previousLesson->id);
        }
    }

    private function checkEnrollment()
    {
        if (Auth::check()) {
            $this->isEnrolled = Auth::user()->enrolledCourses()
                ->where('course_id', $this->course->id)
                ->where('status', 'active')
                ->exists();
        }
    }

    private function checkLessonAccess()
    {
        $this->canWatchLesson = $this->currentLesson->is_free_preview || $this->isEnrolled;
    }

    private function loadProgress()
    {
        if (!Auth::check() || !$this->isEnrolled) {
            $this->progress = [
                'percentage' => 0,
                'position' => 0,
                'is_completed' => false,
            ];
            return;
        }

        $lessonProgress = $this->lessonsProgress[$this->currentLesson->id] ?? null;

        $this->progress = [
            'percentage' => $lessonProgress ? $lessonProgress->watched_percentage : 0,
            'position' => $lessonProgress ? $lessonProgress->last_position_seconds : 0,
            'is_completed' => $lessonProgress ? $lessonProgress->isCompleted() : false,
        ];
    }

    private function loadAllLessonsProgress()
    {
        if (!Auth::check() || !$this->isEnrolled) {
            $this->lessonsProgress = [];
            return;
        }

        $user = Auth::user();
        $lessonIds = $this->course->publishedLessons->pluck('id');
        
        $this->lessonsProgress = $user->lessonProgress()
            ->whereIn('lesson_id', $lessonIds)
            ->get()
            ->keyBy('lesson_id');
    }

    public function hasPreviousLesson()
    {
        if (!$this->currentLesson || $this->currentLesson->order === null) {
            return false;
        }
        
        return $this->course->publishedLessons()
            ->where('order', '<', $this->currentLesson->order)
            ->exists();
    }

    public function hasNextLesson()
    {
        if (!$this->currentLesson || $this->currentLesson->order === null) {
            return false;
        }
        
        return $this->course->publishedLessons()
            ->where('order', '>', $this->currentLesson->order)
            ->exists();
    }

    public function render()
    {
        return view('livewire.course-player');
    }
}
