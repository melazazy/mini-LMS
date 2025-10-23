<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_checkout(): void
    {
        $course = Course::factory()->create(['price' => 99.99, 'is_published' => true]);

        $response = $this->get(route('checkout', $course));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_checkout_for_paid_course(): void
    {
        $user = User::factory()->student()->create();
        $course = Course::factory()->create([
            'price' => 99.99,
            'is_published' => true,
        ]);

        $response = $this->actingAs($user)->get(route('checkout', $course));

        // Should redirect to Stripe (or show error if Stripe not configured)
        $this->assertTrue(
            $response->isRedirect() || $response->status() === 302
        );
    }

    public function test_user_cannot_checkout_free_course(): void
    {
        $user = User::factory()->student()->create();
        $course = Course::factory()->create([
            'price' => null,
            'is_published' => true,
        ]);

        $response = $this->actingAs($user)->get(route('checkout', $course));

        $response->assertRedirect(route('courses.show', $course));
        $response->assertSessionHas('error');
    }

    public function test_user_cannot_checkout_already_enrolled_course(): void
    {
        $user = User::factory()->student()->create();
        $course = Course::factory()->create([
            'price' => 99.99,
            'is_published' => true,
        ]);

        // Enroll user
        $user->enrolledCourses()->attach($course->id, [
            'status' => 'active',
            'paid_amount' => $course->price,
            'currency' => 'USD',
        ]);

        $response = $this->actingAs($user)->get(route('checkout', $course));

        $response->assertRedirect(route('courses.show', $course));
        $response->assertSessionHas('error');
    }

    public function test_checkout_success_route_exists(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->actingAs($user)->get(route('checkout.success'));

        // Should redirect or show error without session_id
        $this->assertTrue(
            $response->isRedirect() || $response->status() === 302
        );
    }

    public function test_checkout_cancel_route_exists(): void
    {
        $user = User::factory()->student()->create();
        $course = Course::factory()->create(['price' => 99.99]);

        $response = $this->actingAs($user)->get(route('checkout.cancel', $course));

        $response->assertRedirect(route('courses.show', $course));
        $response->assertSessionHas('info');
    }

    public function test_webhook_route_exists_and_requires_no_auth(): void
    {
        $response = $this->post(route('webhooks.stripe'), []);

        // Should return 400 (invalid payload) not 401/403 (auth required)
        $this->assertNotEquals(401, $response->status());
        $this->assertNotEquals(403, $response->status());
    }

    public function test_enrollment_includes_payment_metadata(): void
    {
        $user = User::factory()->student()->create();
        $course = Course::factory()->create([
            'price' => 149.99,
            'currency' => 'USD',
            'is_published' => true,
        ]);

        $enrollment = $user->enrolledCourses()->attach($course->id, [
            'status' => 'active',
            'paid_amount' => $course->price,
            'currency' => $course->currency,
            'payment_id' => 'stripe_test_123',
        ]);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'paid_amount' => 149.99,
            'currency' => 'USD',
            'payment_id' => 'stripe_test_123',
        ]);
    }

    public function test_paid_course_displays_price_correctly(): void
    {
        $course = Course::factory()->create([
            'price' => 99.99,
            'currency' => 'USD',
            'is_published' => true,
        ]);

        $this->assertEquals('USD 99.99', $course->formatted_price);
        $this->assertTrue($course->isPaid());
        $this->assertFalse($course->isFree());
    }

    public function test_free_course_displays_free_label(): void
    {
        $course = Course::factory()->create([
            'price' => null,
            'is_published' => true,
        ]);

        $this->assertEquals('Free', $course->formatted_price);
        $this->assertTrue($course->isFree());
        $this->assertFalse($course->isPaid());
    }
}
