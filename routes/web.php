<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstructorDashboardController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [CourseController::class, 'index'])->name('home');
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/courses/{course}/watch/{lesson?}', [CourseController::class, 'watch'])->name('courses.watch')->whereNumber('lesson');

// Public certificate verification
Route::get('/certificates/verify/{hash}', [CertificateController::class, 'verify'])->name('certificates.verify');

// Demo certificate (for testing layout)
Route::get('/certificates/demo', [CertificateController::class, 'demo'])->name('certificates.demo');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/password/reset', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Stripe webhook (no auth/CSRF required)
Route::post('/webhooks/stripe', [WebhookController::class, 'handle'])->name('webhooks.stripe');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Role-specific dashboards
    Route::get('/instructor/dashboard', [InstructorDashboardController::class, 'index'])->name('instructor.dashboard');
    Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
    
    // Course enrollment
    Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');
    
    // Payment routes (specific routes MUST come before parameterized routes)
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/cancel/{course}', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
    Route::get('/checkout/{course}', [CheckoutController::class, 'checkout'])->name('checkout');
    
    // Lesson routes
    Route::get('/courses/{course}/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
    Route::post('/lessons/{lesson}/progress', [LessonController::class, 'updateProgress'])->name('lessons.progress')
        ->middleware('role:student');
    
    // Certificate routes
    Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])
        ->name('certificates.download')
        ->defaults('format', 'pdf');
    
    // Instructor routes
    Route::middleware(['auth', 'role:instructor'])->prefix('instructor')->name('instructor.')->group(function () {
        // Courses with soft delete routes
        Route::resource('courses', CourseController::class)->except(['show', 'index']);
        
        // Soft delete routes for courses
        Route::post('courses/{course}/restore', [CourseController::class, 'restore'])
            ->name('courses.restore');
        Route::delete('courses/{course}/force-delete', [CourseController::class, 'forceDelete'])
            ->name('courses.force-delete');
            
        Route::resource('lessons', LessonController::class)->except(['show']);
    });
    
    // Instructor/Admin routes
    Route::middleware('instructor_or_admin')->group(function () {
        Route::get('/instructor/courses', [CourseController::class, 'index'])->name('instructor.courses.index');
        Route::get('/instructor/courses/create', [CourseController::class, 'create'])->name('instructor.courses.create');
        Route::post('/instructor/courses', [CourseController::class, 'store'])->name('instructor.courses.store');
        Route::get('/instructor/courses/{course}/edit', [CourseController::class, 'edit'])->name('instructor.courses.edit');
        Route::put('/instructor/courses/{course}', [CourseController::class, 'update'])->name('instructor.courses.update');
        Route::delete('/instructor/courses/{course}', [CourseController::class, 'destroy'])->name('instructor.courses.destroy');
        
        Route::get('/instructor/courses/{course}/lessons', [LessonController::class, 'index'])->name('instructor.lessons.index');
        Route::get('/instructor/courses/{course}/lessons/create', [LessonController::class, 'create'])->name('instructor.lessons.create');
        Route::post('/instructor/courses/{course}/lessons', [LessonController::class, 'store'])->name('instructor.lessons.store');
        Route::get('/instructor/lessons/{lesson}/edit', [LessonController::class, 'edit'])->name('instructor.lessons.edit');
        Route::put('/instructor/lessons/{lesson}', [LessonController::class, 'update'])->name('instructor.lessons.update');
        Route::delete('/instructor/lessons/{lesson}', [LessonController::class, 'destroy'])->name('instructor.lessons.destroy');
    });
});