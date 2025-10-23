<?php

use App\Models\User;

test('admin can access filament panel', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $response = $this->actingAs($admin, 'web')->get('/admin');
    
    $response->assertOk();
});

test('instructor cannot access filament panel', function () {
    $instructor = User::factory()->create(['role' => 'instructor']);
    
    $response = $this->actingAs($instructor, 'web')->get('/admin');
    
    $response->assertForbidden();
});

test('student cannot access filament panel', function () {
    $student = User::factory()->create(['role' => 'student']);
    
    $response = $this->actingAs($student, 'web')->get('/admin');
    
    $response->assertForbidden();
});

test('guest is redirected to filament login when accessing admin panel', function () {
    $response = $this->get('/admin');
    
    // Filament uses its own login page at /admin/login
    $response->assertRedirect('/admin/login');
});

test('admin can access courses resource', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $response = $this->actingAs($admin, 'web')->get('/admin/courses');
    
    $response->assertOk();
});

test('admin can access users resource', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $response = $this->actingAs($admin, 'web')->get('/admin/users');
    
    $response->assertOk();
});

test('admin can access lessons resource', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $response = $this->actingAs($admin, 'web')->get('/admin/lessons');
    
    $response->assertOk();
});

test('admin can access enrollments resource', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $response = $this->actingAs($admin, 'web')->get('/admin/enrollments');
    
    $response->assertOk();
});

test('non-admin cannot access courses resource', function () {
    $student = User::factory()->create(['role' => 'student']);
    
    $response = $this->actingAs($student, 'web')->get('/admin/courses');
    
    $response->assertForbidden();
});

test('non-admin cannot access users resource', function () {
    $instructor = User::factory()->create(['role' => 'instructor']);
    
    $response = $this->actingAs($instructor, 'web')->get('/admin/users');
    
    $response->assertForbidden();
});

test('admin can view filament dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $response = $this->actingAs($admin, 'web')->get('/admin');
    
    $response->assertOk();
    // Verify Filament dashboard loads
    expect($response->content())->toContain('Filament');
});

test('filament panel requires authentication', function () {
    $response = $this->get('/admin/courses');
    
    // Filament redirects to its own login page
    $response->assertRedirect('/admin/login');
});

test('authenticated non-admin gets 403 on all filament resources', function () {
    $student = User::factory()->create(['role' => 'student']);
    
    $this->actingAs($student, 'web');
    
    // Test multiple resources - all should return 403
    $this->get('/admin')->assertForbidden();
    $this->get('/admin/courses')->assertForbidden();
    $this->get('/admin/users')->assertForbidden();
    $this->get('/admin/lessons')->assertForbidden();
    $this->get('/admin/enrollments')->assertForbidden();
});

test('only admin role can access filament panel', function () {
    // Test non-admin roles
    $users = [
        User::factory()->create(['role' => 'student']),
        User::factory()->create(['role' => 'instructor']),
    ];
    
    foreach ($users as $user) {
        $response = $this->actingAs($user, 'web')->get('/admin');
        expect($response->status())->toBe(403);
    }
    
    // Test admin role - should succeed
    $admin = User::factory()->create(['role' => 'admin']);
    $response = $this->actingAs($admin, 'web')->get('/admin');
    expect($response->status())->toBe(200);
});

test('ensure user is admin middleware blocks non-admins', function () {
    $student = User::factory()->create(['role' => 'student']);
    $instructor = User::factory()->create(['role' => 'instructor']);
    
    // Both should be blocked
    expect($student->isAdmin())->toBeFalse();
    expect($instructor->isAdmin())->toBeFalse();
    
    $this->actingAs($student, 'web')
        ->get('/admin')
        ->assertForbidden();
        
    $this->actingAs($instructor, 'web')
        ->get('/admin')
        ->assertForbidden();
});

test('admin user helper method works correctly', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = User::factory()->create(['role' => 'student']);
    $instructor = User::factory()->create(['role' => 'instructor']);
    
    expect($admin->isAdmin())->toBeTrue();
    expect($student->isAdmin())->toBeFalse();
    expect($instructor->isAdmin())->toBeFalse();
});
