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
        
        // Update browser URL
        $this->redirect(route('courses.watch', [
            'course' => $this->course->slug,
            'lesson' => $lessonId
        ]), navigate: true);
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

        try {
            $action = app(UpdateLessonProgressAction::class);
            $action->execute(Auth::user(), $this->currentLesson, $percentage, $position);
            
            // Reload all lessons progress to update the sidebar
            $this->loadAllLessonsProgress();
            $this->loadProgress();
            $this->dispatch('progressUpdated');
        } catch (\Exception $e) {
            \Log::error('Error updating progress: ' . $e->getMessage());
        }
    }

    /**
     * Mark the current lesson as complete
     * This triggers course completion logic if all lessons are completed
     */
    public function markAsComplete()
    {
        if (!Auth::check() || !$this->isEnrolled) {
            return;
        }

        // Mark lesson as 100% complete with full duration
        $action = app(UpdateLessonProgressAction::class);
        $action->execute(
            Auth::user(), 
            $this->currentLesson, 
            100, 
            $this->currentLesson->duration_seconds ?? 0
        );
        
        // Reload progress to reflect completion
        $this->loadAllLessonsProgress();
        $this->loadProgress();
        
        // Dispatch events
        $this->dispatch('lessonCompleted');
        $this->dispatch('progressUpdated');
        
        // Show success message
        session()->flash('success', 'Lesson marked as complete!');
    }

    public function nextLesson()
    {
        $nextLesson = $this->course->publishedLessons
            ->where('order', '>', $this->currentLesson->order)
            ->sortBy('order')
            ->first();

        if ($nextLesson) {
            $this->loadLesson($nextLesson->id);
        }
    }

    public function previousLesson()
    {
        $previousLesson = $this->course->publishedLessons
            ->where('order', '<', $this->currentLesson->order)
            ->sortByDesc('order')
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
        // Free preview lessons are accessible to everyone (if published)
        if ($this->currentLesson->is_free_preview && $this->currentLesson->is_published) {
            $this->canWatchLesson = true;
            return;
        }
        
        // Other lessons require enrollment AND must be published
        $this->canWatchLesson = $this->isEnrolled && $this->currentLesson->is_published;
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

    /**
     * Get the completion threshold from config
     */
    public function getCompletionThresholdProperty()
    {
        return config('lms.lesson_completion_threshold', 90);
    }

    /**
     * Get the total number of lessons
     */
    public function getTotalLessonsProperty()
    {
        return $this->course->publishedLessons->count();
    }

    /**
     * Get the number of completed lessons
     */
    public function getCompletedLessonsProperty()
    {
        if (!$this->isEnrolled || !Auth::check()) {
            return 0;
        }

        return $this->lessonsProgress->filter(function($progress) {
            return $progress->watched_percentage >= $this->completionThreshold;
        })->count();
    }

    /**
     * Get the overall course progress percentage
     */
    public function getProgressPercentageProperty()
    {
        if ($this->totalLessons === 0) {
            return 0;
        }

        return round(($this->completedLessons / $this->totalLessons) * 100);
    }

    public function render()
    {
        return view('livewire.course-player');
    }
}
