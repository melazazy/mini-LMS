<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LessonProgress>
 */
class LessonProgressFactory extends Factory
{
    protected $model = LessonProgress::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'lesson_id' => Lesson::factory(),
            'watched_percentage' => $this->faker->numberBetween(0, 100),
            'last_position_seconds' => $this->faker->numberBetween(0, 3600),
            'last_watched_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the lesson is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'watched_percentage' => $this->faker->numberBetween(90, 100),
        ]);
    }

    /**
     * Indicate that the lesson is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'watched_percentage' => $this->faker->numberBetween(1, 89),
        ]);
    }

    /**
     * Indicate that the lesson is not started.
     */
    public function notStarted(): static
    {
        return $this->state(fn (array $attributes) => [
            'watched_percentage' => 0,
            'last_position_seconds' => 0,
        ]);
    }
}
