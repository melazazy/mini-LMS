<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('user can register as student', function () {
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
});

test('user can register as instructor', function () {
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
});

test('user cannot register with invalid role', function () {
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
});

test('user can login with valid credentials', function () {
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
});

test('user cannot login with invalid credentials', function () {
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
});

test('user can logout', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/logout');

    $response->assertRedirect('/');
    $this->assertGuest();
});

test('guest cannot access protected routes', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('student cannot access instructor routes', function () {
    $student = User::factory()->create(['role' => 'student']);
    $this->actingAs($student);

    $response = $this->get('/instructor/courses');
    $response->assertStatus(403);
});

test('instructor can access instructor routes', function () {
    $instructor = User::factory()->create(['role' => 'instructor']);
    $this->actingAs($instructor);

    $response = $this->get('/instructor/courses');
    $response->assertStatus(200);
});

test('admin can access instructor routes', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $response = $this->get('/instructor/courses');
    $response->assertStatus(200);
});

test('password reset request form is accessible', function () {
    $response = $this->get('/password/reset');
    $response->assertStatus(200);
});

test('password reset link can be requested', function () {
    $user = User::factory()->create();

    $response = $this->post('/password/email', [
        'email' => $user->email,
    ]);

    $response->assertSessionHas('status');
});

test('user role methods work correctly', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $instructor = User::factory()->create(['role' => 'instructor']);
    $student = User::factory()->create(['role' => 'student']);

    // Test isAdmin method
    expect($admin->isAdmin())->toBeTrue();
    expect($instructor->isAdmin())->toBeFalse();
    expect($student->isAdmin())->toBeFalse();

    // Test isInstructor method
    expect($admin->isInstructor())->toBeFalse();
    expect($instructor->isInstructor())->toBeTrue();
    expect($student->isInstructor())->toBeFalse();

    // Test isStudent method
    expect($admin->isStudent())->toBeFalse();
    expect($instructor->isStudent())->toBeFalse();
    expect($student->isStudent())->toBeTrue();

    // Test canManageContent method
    expect($admin->canManageContent())->toBeTrue();
    expect($instructor->canManageContent())->toBeTrue();
    expect($student->canManageContent())->toBeFalse();
});

test('registration form is accessible', function () {
    $response = $this->get('/register');
    $response->assertStatus(200);
});

test('login form is accessible', function () {
    $response = $this->get('/login');
    $response->assertStatus(200);
});

test('authenticated user can access dashboard', function () {
    $user = User::factory()->student()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    // Dashboard redirects to role-specific dashboard
    $response->assertRedirect(route('student.dashboard'));
});

test('remember me functionality', function () {
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
    expect($user->fresh()->remember_token)->not->toBeNull();
});
