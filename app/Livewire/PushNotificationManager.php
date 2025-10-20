<?php

namespace App\Livewire;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Component for managing push notification subscriptions.
 * Handles subscription and unsubscription to web push notifications.
 */
class PushNotificationManager extends Component
{
    public bool $isSupported = false;
    public bool $isSubscribed = false;
    public bool $isLoading = false;

    /**
     * Mount the component and check subscription status.
     */
    public function mount(): void
    {
        $this->isSupported = true; // Will be checked on client-side
        $this->isSubscribed = $this->checkSubscription();
    }

    /**
     * Initiate push notification subscription.
     */
    public function subscribe(): void
    {
        if (!$this->isSupported) {
            return;
        }

        $this->isLoading = true;
        $this->dispatch('request-push-permission');
    }

    /**
     * Unsubscribe from push notifications.
     */
    public function unsubscribe(): void
    {
        if (!Auth::check()) {
            return;
        }

        PushSubscription::where('user_id', Auth::id())->delete();
        $this->isSubscribed = false;
        
        session()->flash('success', 'Push notifications disabled.');
    }

    /**
     * Handle subscription data from client.
     */
    public function handleSubscription(array $subscription): void
    {
        if (!Auth::check()) {
            return;
        }

        PushSubscription::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'endpoint' => $subscription['endpoint'],
            ],
            [
                'public_key' => $subscription['keys']['p256dh'],
                'auth_token' => $subscription['keys']['auth'],
            ]
        );

        $this->isSubscribed = true;
        $this->isLoading = false;
        
        session()->flash('success', 'Push notifications enabled!');
    }

    /**
     * Check if user has an active subscription.
     */
    private function checkSubscription(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return PushSubscription::where('user_id', Auth::id())->exists();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.push-notification-manager');
    }
}
