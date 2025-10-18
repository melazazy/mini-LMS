@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="border-4 border-dashed border-gray-200 rounded-lg h-96 flex items-center justify-center">
                <div class="text-center">
                    <h1 class="text-2xl font-bold text-gray-900 mb-4">Welcome to your Dashboard!</h1>
                    <p class="text-gray-600">You are logged in as: {{ auth()->user()->name }} ({{ auth()->user()->role }})</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
