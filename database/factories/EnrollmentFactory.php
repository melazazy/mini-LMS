<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Enrollment>
 */
class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'status' => 'active',
            'paid_amount' => $this->faker->randomFloat(2, 0, 100),
            'currency' => 'USD',
            'payment_id' => $this->faker->uuid(),
        ];
    }

    /**
     * Indicate that the enrollment is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the enrollment is canceled.
     */
    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'canceled',
        ]);
    }

    /**
     * Indicate that the enrollment is free.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_amount' => null,
            'currency' => null,
            'payment_id' => null,
        ]);
    }
}
