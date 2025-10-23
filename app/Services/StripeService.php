<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Log;

/**
 * Service class for Stripe payment operations
 * Follows Action pattern - single responsibility for payment processing
 */
class StripeService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe checkout session for course enrollment
     * 
     * @param Course $course
     * @param User $user
     * @return array
     */
    public function createCheckoutSession(Course $course, User $user): array
    {
        try {
            $session = $this->stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => strtolower($course->currency ?? 'usd'),
                            'product_data' => [
                                'name' => $course->title,
                                'description' => $course->description ?? 'Online Course',
                                'images' => $course->thumbnail_url ? [$course->thumbnail_url] : [],
                            ],
                            'unit_amount' => (int)($course->price * 100), // Convert to cents
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('courses.show', $course),
                'customer_email' => $user->email,
                'metadata' => [
                    'course_id' => $course->id,
                    'user_id' => $user->id,
                ],
            ]);

            Log::info('Stripe checkout session created', [
                'session_id' => $session->id,
                'course_id' => $course->id,
                'user_id' => $user->id,
            ]);

            return [
                'success' => true,
                'session_id' => $session->id,
                'url' => $session->url,
            ];
        } catch (ApiErrorException $e) {
            Log::error('Stripe checkout session creation failed', [
                'error' => $e->getMessage(),
                'course_id' => $course->id,
                'user_id' => $user->id,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve a Stripe checkout session
     * 
     * @param string $sessionId
     * @return object|null
     */
    public function retrieveSession(string $sessionId): ?object
    {
        try {
            return $this->stripe->checkout->sessions->retrieve($sessionId);
        } catch (ApiErrorException $e) {
            Log::error('Stripe session retrieval failed', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
            ]);
            
            return null;
        }
    }
}
