<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

/**
 * Handles Stripe webhook events
 * Ensures payment integrity and enrollment status updates
 */
class WebhookController extends Controller
{
    /**
     * Handle incoming Stripe webhook
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        // Verify webhook signature for security
        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid payload in Stripe webhook', [
                'error' => $e->getMessage(),
            ]);
            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            Log::error('Invalid signature in Stripe webhook', [
                'error' => $e->getMessage(),
            ]);
            return response('Invalid signature', 400);
        }

        Log::info('Stripe webhook received', [
            'type' => $event->type,
            'id' => $event->id,
        ]);

        // Handle different event types
        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object);
                break;
            
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;
            
            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;
            
            default:
                Log::info('Unhandled Stripe webhook event type', [
                    'type' => $event->type,
                ]);
        }

        return response('OK', 200);
    }

    /**
     * Handle successful checkout session
     */
    protected function handleCheckoutSessionCompleted($session)
    {
        Log::info('Checkout session completed', [
            'session_id' => $session->id,
            'payment_status' => $session->payment_status,
        ]);
        
        // Enrollment is handled in the success callback
        // This webhook is for additional processing/logging if needed
    }

    /**
     * Handle successful payment intent
     */
    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        Log::info('Payment intent succeeded', [
            'payment_intent_id' => $paymentIntent->id,
        ]);
        
        // Update enrollment status if needed
        $enrollment = Enrollment::where('payment_id', $paymentIntent->id)->first();
        
        if ($enrollment && $enrollment->status !== 'active') {
            $enrollment->update(['status' => 'active']);
            
            Log::info('Enrollment activated via webhook', [
                'enrollment_id' => $enrollment->id,
                'payment_intent_id' => $paymentIntent->id,
            ]);
        }
    }

    /**
     * Handle failed payment intent
     */
    protected function handlePaymentIntentFailed($paymentIntent)
    {
        Log::warning('Payment intent failed', [
            'payment_intent_id' => $paymentIntent->id,
            'failure_message' => $paymentIntent->last_payment_error->message ?? 'Unknown error',
        ]);
        
        // Mark enrollment as canceled if exists
        $enrollment = Enrollment::where('payment_id', $paymentIntent->id)->first();
        
        if ($enrollment) {
            $enrollment->update(['status' => 'canceled']);
            
            Log::info('Enrollment canceled due to payment failure', [
                'enrollment_id' => $enrollment->id,
                'payment_intent_id' => $paymentIntent->id,
            ]);
        }
    }
}
