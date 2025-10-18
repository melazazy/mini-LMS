<?php

namespace Database\Factories;

use App\Models\ModerationReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ModerationReview>
 */
class ModerationReviewFactory extends Factory
{
    protected $model = ModerationReview::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_type' => $this->faker->randomElement(['course', 'lesson']),
            'subject_id' => $this->faker->numberBetween(1, 100),
            'state' => $this->faker->randomElement(['draft', 'pending', 'approved', 'rejected']),
            'reviewer_id' => User::factory(),
            'submitted_by' => User::factory(),
            'notes' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that the review is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'pending',
        ]);
    }

    /**
     * Indicate that the review is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'approved',
        ]);
    }

    /**
     * Indicate that the review is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'rejected',
        ]);
    }

    /**
     * Indicate that the review is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'draft',
        ]);
    }
}
