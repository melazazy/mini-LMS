@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12 transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                Explore Our Courses
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-300">
                Learn new skills with our comprehensive video courses
            </p>
        </div>

        <livewire:course-list />
    </div>
</div>
@endsection
