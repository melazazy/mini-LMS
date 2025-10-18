<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_as_student()
    {
        $response = $this->post('/register', [
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'email' => 'student@example.com',
            'role' => 'student',
        ]);
        $this->assertAuthenticated();
    }

    public function test_user_can_register_as_instructor()
    {
        $response = $this->post('/register', [
            'name' => 'Test Instructor',
            'email' => 'instructor@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'instructor',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'email' => 'instructor@example.com',
            'role' => 'instructor',
        ]);
        $this->assertAuthenticated();
    }

    public function test_user_cannot_register_with_invalid_role()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin', // Admin role should not be allowed during registration
        ]);

        $response->assertSessionHasErrors(['role']);
        $this->assertDatabaseMissing('users', [
            'email' => 'user@example.com',
        ]);
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_guest_cannot_access_protected_routes()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_student_cannot_access_instructor_routes()
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);

        $response = $this->get('/instructor/courses');
        $response->assertStatus(403);
    }

    public function test_instructor_can_access_instructor_routes()
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $this->actingAs($instructor);

        $response = $this->get('/instructor/courses');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_instructor_routes()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/instructor/courses');
        $response->assertStatus(200);
    }

    public function test_password_reset_request_form_is_accessible()
    {
        $response = $this->get('/password/reset');
        $response->assertStatus(200);
    }

    public function test_password_reset_link_can_be_requested()
    {
        $user = User::factory()->create();

        $response = $this->post('/password/email', [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status');
    }

    public function test_user_role_methods_work_correctly()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student']);

        // Test isAdmin method
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($instructor->isAdmin());
        $this->assertFalse($student->isAdmin());

        // Test isInstructor method
        $this->assertFalse($admin->isInstructor());
        $this->assertTrue($instructor->isInstructor());
        $this->assertFalse($student->isInstructor());

        // Test isStudent method
        $this->assertFalse($admin->isStudent());
        $this->assertFalse($instructor->isStudent());
        $this->assertTrue($student->isStudent());

        // Test canManageContent method
        $this->assertTrue($admin->canManageContent());
        $this->assertTrue($instructor->canManageContent());
        $this->assertFalse($student->canManageContent());
    }

    public function test_registration_form_is_accessible()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_login_form_is_accessible()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_remember_me_functionality()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        
        // Check if remember token is set
        $this->assertNotNull($user->fresh()->remember_token);
    }
}