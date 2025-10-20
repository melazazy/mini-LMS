<?php

namespace App\Livewire;

use App\Actions\Enrollment\EnrollInCourseAction;
use App\Actions\Enrollment\EnrollInFreeCourseAction;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EnrollmentButton extends Component
{
    public Course $course;
    public $isEnrolled = false;
    public $isLoading = false;

    public function mount(Course $course)
    {
        $this->course = $course;
        $this->checkEnrollment();
    }

    public function enroll()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->isLoading = true;

        try {
            if ($this->course->isFree()) {
                $action = app(EnrollInFreeCourseAction::class);
                $action->execute(Auth::user(), $this->course);
                
                $this->isEnrolled = true;
                $this->dispatch('enrolled', courseId: $this->course->id);
                session()->flash('success', 'Successfully enrolled in ' . $this->course->title);
            } else {
                // For paid courses, redirect to payment
                return redirect()->route('checkout', $this->course);
            }
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        } finally {
            $this->isLoading = false;
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

    public function render()
    {
        return view('livewire.enrollment-button');
    }
}
