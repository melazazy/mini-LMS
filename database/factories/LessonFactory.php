<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(3);
        
        return [
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title),
            'video_url' => $this->faker->url(),
            'duration_seconds' => $this->faker->numberBetween(60, 3600), // 1 minute to 1 hour
            'order' => $this->faker->numberBetween(1, 10),
            'is_free_preview' => $this->faker->boolean(20), // 20% chance of being free preview
            'is_published' => $this->faker->boolean(80), // 80% chance of being published
            'course_id' => Course::factory(),
        ];
    }

    /**
     * Indicate that the lesson is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
        ]);
    }

    /**
     * Indicate that the lesson is unpublished.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }

    /**
     * Indicate that the lesson is a free preview.
     */
    public function freePreview(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_free_preview' => true,
            'is_published' => true,
        ]);
    }
}