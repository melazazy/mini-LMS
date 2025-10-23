<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Success Icon -->
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    Payment Successful!
                </h1>
                
                <p class="text-lg text-gray-600 mb-8">
                    You're now enrolled in <strong>{{ $course->title }}</strong>
                </p>
            </div>

            <!-- Course Details Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                @if($course->thumbnail_url)
                    <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="w-full h-48 object-cover">
                @endif
                
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">
                        {{ $course->title }}
                    </h2>
                    
                    <div class="flex items-center justify-between text-sm text-gray-600 mb-4">
                        <span class="inline-flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"></path>
                            </svg>
                            {{ ucfirst($course->level) }}
                        </span>
                        
                        <span class="font-semibold text-green-600">
                            {{ $course->formatted_price }}
                        </span>
                    </div>

                    @if($course->description)
                        <p class="text-gray-600 text-sm mb-4">
                            {{ Str::limit($course->description, 150) }}
                        </p>
                    @endif

                    <!-- Payment Details -->
                    <div class="border-t pt-4 mt-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Payment Details</h3>
                        <div class="text-sm text-gray-600 space-y-1">
                            <div class="flex justify-between">
                                <span>Transaction ID:</span>
                                <span class="font-mono text-xs">{{ Str::limit($session->id, 20) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Status:</span>
                                <span class="text-green-600 font-semibold">{{ ucfirst($session->payment_status) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <a href="{{ route('courses.show', $course) }}" 
                   class="w-full flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Start Learning
                </a>
                
                <a href="{{ route('dashboard') }}" 
                   class="w-full flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    Go to Dashboard
                </a>
                
                <a href="{{ route('courses.index') }}" 
                   class="w-full flex items-center justify-center px-6 py-3 text-base font-medium text-indigo-600 hover:text-indigo-500">
                    Browse More Courses
                </a>
            </div>

            <!-- Email Confirmation Notice -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-md p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            A confirmation email has been sent to <strong>{{ auth()->user()->email }}</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
