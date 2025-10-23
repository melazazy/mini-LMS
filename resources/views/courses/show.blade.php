@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12 transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden transition-colors duration-200">
            <!-- Course Header -->
            <div class="relative h-64 bg-gradient-to-r from-indigo-500 to-purple-600">
                @if($course->thumbnail_url)
                    <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="w-full h-full object-cover opacity-50">
                @endif
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center text-white">
                        <h1 class="text-4xl font-bold mb-2">{{ $course->title }}</h1>
                        <p class="text-xl">{{ $course->description }}</p>
                    </div>
                </div>
            </div>

            <!-- Course Info -->
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    <!-- Course Details -->
                    <div class="md:col-span-2">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">About This Course</h2>
                        <p class="text-gray-700 dark:text-gray-300 mb-6">{{ $course->description }}</p>

                        <div class="flex items-center space-x-6 mb-6">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">{{ $course->publishedLessons->count() }} Lessons</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">{{ ucfirst($course->level) }}</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">{{ $course->creator->name }}</span>
                            </div>
                        </div>

                        <!-- Course Content - Alpine.js Collapsible Accordion -->
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Course Content</h3>
                        <div x-data="{ openLesson: null }" class="space-y-2">
                            @foreach($course->publishedLessons as $index => $lesson)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                    <!-- Accordion Header -->
                                    <button 
                                        @click="openLesson = openLesson === {{ $index }} ? null : {{ $index }}"
                                        class="w-full flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 bg-white dark:bg-gray-800"
                                    >
                                        <div class="flex items-center flex-1">
                                            <span class="text-gray-500 font-medium mr-3">{{ $index + 1 }}.</span>
                                            @if($lesson->is_free_preview)
                                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full mr-3">
                                                    Free Preview
                                                </span>
                                            @else
                                                <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                            @endif
                                            <span class="font-medium text-gray-900 dark:text-white text-left">{{ $lesson->title }}</span>
                                        </div>
                                        
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm text-gray-500">{{ $lesson->formatted_duration }}</span>
                                            <svg 
                                                class="w-5 h-5 text-gray-500 transition-transform duration-200"
                                                :class="{ 'rotate-180': openLesson === {{ $index }} }"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </button>
                                    
                                    <!-- Accordion Content -->
                                    <div 
                                        x-show="openLesson === {{ $index }}"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                        class="px-4 pb-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700"
                                        style="display: none;"
                                    >
                                        @if($lesson->description)
                                            <p class="text-gray-600 dark:text-gray-300 text-sm mt-3 mb-3">{{ $lesson->description }}</p>
                                        @else
                                            <p class="text-gray-500 text-sm italic mt-3 mb-3">No description available</p>
                                        @endif
                                        
                                        @if($lesson->resources && count($lesson->resources) > 0)
                                            <div class="mt-3">
                                                <span class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Resources:</span>
                                                <ul class="text-sm text-gray-600 mt-2 space-y-1">
                                                    @foreach($lesson->resources as $resource)
                                                        <li class="flex items-start">
                                                            <svg class="w-4 h-4 text-indigo-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                            </svg>
                                                            {{ $resource }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        
                                        @php
                                            $isEnrolled = auth()->check() && auth()->user()->enrolledCourses()->where('courses.id', $course->id)->exists();
                                        @endphp
                                        @if($lesson->is_free_preview || $isEnrolled)
                                            <a href="{{ route('courses.watch', ['course' => $course, 'lesson' => $lesson->id]) }}" 
                                               class="mt-4 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                @if($isEnrolled)
                                                    Watch Lesson
                                                @else
                                                    Watch Preview
                                                @endif
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Enrollment Card -->
                    <div class="md:col-span-1">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 sticky top-4 transition-colors duration-200">
                            <div class="text-center mb-6">
                                <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                    {{ $course->formatted_price }}
                                </div>
                                @if($course->isFree())
                                    <p class="text-sm text-gray-600 dark:text-gray-300">Full access to all lessons</p>
                                @else
                                    <p class="text-sm text-gray-600 dark:text-gray-300">One-time payment</p>
                                @endif
                            </div>

                            <livewire:enrollment-button :course="$course" />

                            @php
                                $isEnrolled = auth()->check() && auth()->user()->enrolledCourses()->where('courses.id', $course->id)->exists();
                                $firstLesson = $course->publishedLessons()->orderBy('order')->first();
                            @endphp
                            @if($firstLesson)
                                <a href="{{ route('courses.watch', ['course' => $course, 'lesson' => $firstLesson->id]) }}" 
                                   class="mt-4 w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    @if($isEnrolled)
                                        Continue Learning
                                    @else
                                        Preview Course
                                    @endif
                                </a>
                            @endif

                            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-600">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">This course includes:</h4>
                                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                    <li class="flex items-center">
                                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $course->publishedLessons->count() }} video lessons
                                    </li>
                                    <li class="flex items-center">
                                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Lifetime access
                                    </li>
                                    <li class="flex items-center">
                                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Progress tracking
                                    </li>
                                    <li class="flex items-center">
                                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Certificate of completion
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
