@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($found)
            <!-- Certificate Found -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h1 class="text-2xl font-bold text-white">Certificate Verified</h1>
                                <p class="text-indigo-100 text-sm">This certificate is authentic and valid</p>
                            </div>
                        </div>
                        @if($certificate->isApproved())
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Active
                            </span>
                        @elseif($certificate->isRevoked())
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                Revoked
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Certificate Details -->
                <div class="px-8 py-8">
                    <!-- Student Info -->
                    <div class="mb-8">
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Certificate Holder</h2>
                        <p class="text-3xl font-bold text-gray-900">{{ $certificate->user->name }}</p>
                    </div>

                    <!-- Course Info -->
                    <div class="mb-8">
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Course Completed</h2>
                        <p class="text-2xl font-semibold text-indigo-600 mb-2">{{ $certificate->course->title }}</p>
                        <div class="flex items-center space-x-4 text-sm text-gray-600">
                            <span class="inline-flex items-center">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Level: {{ ucfirst($certificate->course->level) }}
                            </span>
                            <span class="inline-flex items-center">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Instructor: {{ $certificate->course->creator->name }}
                            </span>
                        </div>
                    </div>

                    <!-- Certificate Details Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Certificate Number</h3>
                            <p class="text-lg font-mono font-semibold text-gray-900">{{ $certificate->certificate_number }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Issue Date</h3>
                            <p class="text-lg font-semibold text-gray-900">{{ $certificate->issued_at->format('F d, Y') }}</p>
                        </div>

                        @if($certificate->issuer)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Issued By</h3>
                            <p class="text-lg font-semibold text-gray-900">{{ $certificate->issuer->name }}</p>
                        </div>
                        @endif

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Status</h3>
                            <p class="text-lg font-semibold {{ $certificate->isApproved() ? 'text-green-600' : 'text-red-600' }}">
                                {{ ucfirst($certificate->status) }}
                            </p>
                        </div>
                    </div>

                    @if($certificate->isRevoked())
                    <!-- Revocation Notice -->
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Certificate Revoked</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p><strong>Revoked on:</strong> {{ $certificate->revoked_at->format('F d, Y') }}</p>
                                    @if($certificate->revocation_reason)
                                    <p class="mt-1"><strong>Reason:</strong> {{ $certificate->revocation_reason }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Verification Info -->
                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-indigo-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-indigo-900 mb-1">Verification Information</h3>
                                <p class="text-sm text-indigo-700">
                                    This certificate has been verified against our records. The information displayed above is accurate as of {{ now()->format('F d, Y \a\t H:i') }}.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-8 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600">
                            Issued by <span class="font-semibold">Mini LMS</span>
                        </p>
                        <a href="{{ route('home') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                            Visit Platform →
                        </a>
                    </div>
                </div>
            </div>
        @else
            <!-- Certificate Not Found -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="px-8 py-12 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Certificate Not Found</h2>
                    <p class="text-gray-600 mb-8 max-w-md mx-auto">{{ $message }}</p>
                    <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Return to Home
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
