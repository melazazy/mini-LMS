<?php

namespace App\Http\Controllers;

use App\Actions\Enrollment\EnrollInCourseAction;
use App\Models\Course;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Handles Stripe checkout flow for course enrollment
 * Follows constitution: slim controllers, business logic in Actions/Services
 */
class CheckoutController extends Controller
{
    protected StripeService $stripeService;
    protected EnrollInCourseAction $enrollAction;

    public function __construct(StripeService $stripeService, EnrollInCourseAction $enrollAction)
    {
        $this->stripeService = $stripeService;
        $this->enrollAction = $enrollAction;
    }

    /**
     * Initiate checkout session for a paid course
     */
    public function checkout(Course $course)
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Please login to enroll in courses.');
        }

        // Validate course is paid
        if ($course->isFree()) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'This course is free. Please use the free enrollment option.');
        }

        // Check if already enrolled
        if (Auth::user()->enrolledCourses()->where('course_id', $course->id)->exists()) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'You are already enrolled in this course.');
        }

        // Create Stripe checkout session
        $result = $this->stripeService->createCheckoutSession($course, Auth::user());

        if (!$result['success']) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Payment processing failed. Please try again.');
        }

        return redirect($result['url']);
    }

    /**
     * Handle successful payment callback
     */
    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            return redirect()->route('courses.index')
                ->with('error', 'Invalid payment session.');
        }

        // Retrieve session from Stripe
        $session = $this->stripeService->retrieveSession($sessionId);

        if (!$session || $session->payment_status !== 'paid') {
            return redirect()->route('courses.index')
                ->with('error', 'Payment was not successful.');
        }

        $courseId = $session->metadata->course_id ?? null;
        $userId = $session->metadata->user_id ?? null;

        if (!$courseId || !$userId) {
            return redirect()->route('courses.index')
                ->with('error', 'Invalid payment data.');
        }

        $course = Course::findOrFail($courseId);
        $user = Auth::user();

        // Security: Verify user matches session
        if ($user->id != $userId) {
            return redirect()->route('courses.index')
                ->with('error', 'Unauthorized access.');
        }

        // Enroll user using Action (transactional, idempotent)
        try {
            $this->enrollAction->execute($user, $course, [
                'payment_id' => $session->id,
                'payment_intent' => $session->payment_intent ?? null,
            ]);

            Log::info('Course enrollment successful after payment', [
                'user_id' => $user->id,
                'course_id' => $course->id,
                'session_id' => $session->id,
            ]);

            return view('checkout.success', [
                'course' => $course,
                'session' => $session,
            ]);
        } catch (\Exception $e) {
            Log::error('Course enrollment failed after payment', [
                'user_id' => $user->id,
                'course_id' => $course->id,
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('courses.show', $course)
                ->with('error', 'Payment successful but enrollment failed. Please contact support.');
        }
    }

    /**
     * Handle cancelled payment
     */
    public function cancel(Course $course)
    {
        return redirect()->route('courses.show', $course)
            ->with('info', 'Payment was cancelled. You can try again anytime.');
    }
}
