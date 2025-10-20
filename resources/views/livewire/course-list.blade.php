<div>
    <!-- Search and Filters -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <input
                    wire:model.debounce.300ms="search"
                    type="text"
                    placeholder="Search courses..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
            </div>

            <!-- Level Filter -->
            <div class="sm:w-48">
                <select
                    wire:model="level"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="">All Levels</option>
                    <option value="beginner">Beginner</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="advanced">Advanced</option>
                </select>
            </div>

            <!-- Sort -->
            <div class="sm:w-48">
                <select
                    wire:model="sortBy"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="created_at">Newest</option>
                    <option value="title">Title</option>
                    <option value="level">Level</option>
                    <option value="price">Price</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Courses Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($courses as $course)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <!-- Course Thumbnail -->
                <div class="relative">
                    <img
                        src="{{ $course->thumbnail_url ?: 'https://via.placeholder.com/400x225/3b82f6/ffffff?text=' . urlencode($course->title) }}"
                        alt="{{ $course->title }}"
                        class="w-full h-48 object-cover"
                    >
                    @if($course->isFree())
                        <div class="absolute top-4 left-4">
                            <span class="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                                Free
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Course Content -->
                <div class="p-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($course->level === 'beginner') bg-blue-100 text-blue-800
                            @elseif($course->level === 'intermediate') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($course->level) }}
                        </span>
                        <span class="text-sm text-gray-500">
                            {{ $course->published_lessons_count }} lessons
                        </span>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                        {{ $course->title }}
                    </h3>

                    <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                        {{ $course->description }}
                    </p>

                    <div class="flex items-center justify-between">
                        <div class="text-lg font-bold text-gray-900">
                            {{ $course->formatted_price }}
                        </div>

                        <a
                            href="{{ route('courses.show', $course) }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            View Course
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No courses found</h3>
                <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $courses->links() }}
    </div>
</div>
