@extends('layouts.app')

@section('content')
<!-- Navigation Header -->
<nav class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Back to Course -->
            <div class="flex items-center">
                <a href="{{ route('courses.show', $course) }}" class="flex items-center text-gray-600 hover:text-gray-900">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span class="font-medium">{{ $course->title }}</span>
                </a>
            </div>

            <!-- User Menu -->
            {{-- <div class="flex items-center space-x-4">
                @auth
                    <span class="text-sm text-gray-600">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900">Login</a>
                @endauth
            </div> --}}
        </div>
    </div>
</nav>

<div class="min-h-screen bg-gray-50">
    <livewire:course-player :course="$course" :lesson="$lesson" />
</div>
@endsection
