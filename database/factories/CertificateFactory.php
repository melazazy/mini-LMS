<?php

namespace Database\Factories;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Certificate>
 */
class CertificateFactory extends Factory
{
    protected $model = Certificate::class;

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
            'enrollment_id' => Enrollment::factory(),
            'status' => 'pending',
        ];
    }

    /**
     * Indicate that the certificate is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'issued_at' => now(),
            'issued_by' => User::factory()->admin(),
        ]);
    }

    /**
     * Indicate that the certificate is revoked.
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'revoked',
            'issued_at' => now()->subDays(30),
            'issued_by' => User::factory()->admin(),
            'revoked_at' => now(),
            'revoked_by' => User::factory()->admin(),
            'revocation_reason' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the certificate has files generated.
     */
    public function withFiles(): static
    {
        return $this->state(fn (array $attributes) => [
            'pdf_path' => 'certificates/1/certificate_test.pdf',
            'png_path' => 'certificates/1/certificate_test.png',
        ]);
    }
}
