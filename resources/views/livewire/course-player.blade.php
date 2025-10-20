<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Video Player -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                @if($canWatchLesson)
                    <!-- Fixed aspect ratio container to prevent layout shift -->
                    <div class="relative aspect-video bg-black" 
                         wire:key="video-container-{{ $currentLesson->id }}"
                         x-data="videoPlayer({{ $progress['position'] }})"
                         x-init="init()"
                         @destroy.window="destroy()">
                        <!-- Loading skeleton -->
                        <div class="absolute inset-0 flex items-center justify-center bg-gray-900" wire:loading wire:target="loadLesson">
                            <div class="text-center">
                                <svg class="animate-spin h-12 w-12 text-white mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-white text-sm">Loading lesson...</p>
                            </div>
                        </div>
                        
                        <div class="absolute inset-0">
                            <video 
                                id="video-player-{{ $currentLesson->id }}"
                                class="plyr-player w-full h-full"
                                controls
                                playsinline
                                @if($course->thumbnail_url)
                                    data-poster="{{ $course->thumbnail_url }}"
                                @endif
                            >
                                <source src="{{ $currentLesson->video_url }}" type="video/mp4">
                                @if($currentLesson->hls_manifest_url)
                                    <source src="{{ $currentLesson->hls_manifest_url }}" type="application/x-mpegURL">
                                @endif
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>
                @else
                    <div class="aspect-video bg-gray-900 flex items-center justify-center">
                        <div class="text-center text-white p-8">
                            <svg class="mx-auto h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <h3 class="text-lg font-medium mb-2">Lesson Locked</h3>
                            <p class="text-gray-300 mb-4">
                                @if(!$isEnrolled)
                                    Please enroll in this course to access all lessons.
                                @else
                                    This lesson is not available yet.
                                @endif
                            </p>
                            @if(!$isEnrolled)
                                <livewire:enrollment-button :course="$course" />
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Lesson Info -->
                <div class="p-6 border-t border-gray-200">
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">
                            {{ $currentLesson->title }}
                        </h1>
                        <div class="flex items-center text-sm text-gray-500 mb-3">
                            <span class="mr-4">Duration: {{ $currentLesson->formatted_duration }}</span>
                            @if($currentLesson->is_free_preview)
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">
                                    Free Preview
                                </span>
                            @endif
                        </div>
                        
                        @if($currentLesson->description)
                            <div class="prose prose-sm max-w-none text-gray-600">
                                <p>{{ $currentLesson->description }}</p>
                            </div>
                        @endif
                    </div>

                    @if($progress['is_completed'])
                        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-4">
                            <div class="flex">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">
                                        Lesson Completed!
                                    </h3>
                                    <p class="text-sm text-green-700 mt-1">
                                        Great job! You can now proceed to the next lesson.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Navigation -->
                    <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                        <button 
                            wire:click="previousLesson"
                            @disabled(!$this->hasPreviousLesson())
                            class="inline-flex items-center px-5 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" wire:loading.remove wire:target="previousLesson">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            <svg class="animate-spin h-5 w-5 mr-2" wire:loading wire:target="previousLesson" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Previous Lesson
                        </button>

                        <button 
                            wire:click="nextLesson"
                            @disabled(!$this->hasNextLesson())
                            class="inline-flex items-center px-5 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span wire:loading.remove wire:target="nextLesson">Next Lesson</span>
                            <span wire:loading wire:target="nextLesson">Loading...</span>
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" wire:loading.remove wire:target="nextLesson">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <svg class="animate-spin h-5 w-5 ml-2" wire:loading wire:target="nextLesson" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden sticky top-4">
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
                    <h3 class="text-lg font-semibold text-gray-900">Course Content</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $course->publishedLessons->count() }} lessons
                    </p>
                    
                    @if($isEnrolled && Auth::check())
                        @php
                            $completionThreshold = config('lms.lesson_completion_threshold', 90);
                            $totalLessons = $course->publishedLessons->count();
                            $completedLessons = $lessonsProgress->filter(function($progress) use ($completionThreshold) {
                                return $progress->watched_percentage >= $completionThreshold;
                            })->count();
                            $progressPercentage = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
                        @endphp
                        
                        <div class="mt-3">
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span>Progress</span>
                                <span class="font-medium">{{ $completedLessons }}/{{ $totalLessons }} completed</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progressPercentage }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="p-4 space-y-1 max-h-[calc(100vh-200px)] overflow-y-auto">
                    @foreach($course->publishedLessons as $lesson)
                        <button
                            wire:click="loadLesson({{ $lesson->id }})"
                            wire:loading.attr="disabled"
                            wire:target="loadLesson"
                            class="w-full text-left p-3 rounded-md transition-colors duration-200 disabled:opacity-50
                                {{ $lesson->id === $currentLesson->id 
                                    ? 'bg-indigo-100 text-indigo-900 border border-indigo-200' 
                                    : 'hover:bg-gray-50 text-gray-700' 
                                }}"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        @if($lesson->is_free_preview)
                                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full mr-2">
                                                Free
                                            </span>
                                        @endif
                                        <span class="text-sm font-medium">{{ $lesson->title }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $lesson->formatted_duration }}
                                    </div>
                                </div>
                                
                                @if($isEnrolled && Auth::check())
                                    @php
                                        $lessonProgress = $lessonsProgress[$lesson->id] ?? null;
                                    @endphp
                                    
                                    @if($lessonProgress && $lessonProgress->isCompleted())
                                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    @elseif($lessonProgress && $lessonProgress->isInProgress())
                                        <div class="w-5 h-5 border-2 border-indigo-500 rounded-full flex items-center justify-center">
                                            <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
    /* Prevent layout shift during video loading */
    .plyr-player {
        min-height: 100%;
    }
    
    .plyr--video {
        height: 100%;
    }
    
    /* Ensure Plyr maintains aspect ratio */
    .plyr--video .plyr__video-wrapper {
        height: 100%;
    }
</style>

<script>
// Global player instances tracker
window.videoPlayerInstances = window.videoPlayerInstances || [];

// Clean up all players before Livewire updates
document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.removing', ({ el }) => {
        // Find any video players in the element being removed
        const videoContainers = el.querySelectorAll('[x-data]');
        videoContainers.forEach(container => {
            if (container.__x && container.__x.$data && typeof container.__x.$data.destroy === 'function') {
                container.__x.$data.destroy();
            }
        });
    });
});

function videoPlayer(initialPosition) {
    return {
        player: null,
        initialized: false,
        videoElement: null,
        
        init() {
            // Prevent multiple initializations
            if (this.initialized) {
                return;
            }
            
            this.$nextTick(() => {
                // Find the video element within this container
                this.videoElement = this.$el.querySelector('video');
                
                if (!this.videoElement) {
                    console.error('Video element not found');
                    return;
                }
                
                // Cleanup any existing player instance and properties
                if (this.videoElement.plyr) {
                    try {
                        this.videoElement.plyr.destroy();
                    } catch (e) {
                        console.warn('Error destroying existing player:', e);
                    }
                }
                
                // Remove all Plyr-added properties and data
                delete this.videoElement.plyr;
                
                // Remove any Plyr-specific attributes that might cause conflicts
                ['quality', 'speed', 'loop', 'language'].forEach(prop => {
                    try {
                        delete this.videoElement[prop];
                    } catch (e) {
                        // Property might be non-configurable, ignore
                    }
                });
                
                if (typeof Plyr === 'undefined') {
                    console.error('Plyr is not loaded');
                    return;
                }
                
                // Use requestAnimationFrame for smoother initialization
                requestAnimationFrame(() => {
                    if (!this.videoElement) return;
                    
                    try {
                        // Set ratio to prevent layout shift
                        this.player = new Plyr(this.videoElement, {
                            ratio: '16:9',
                            controls: [
                                'play-large',
                                'restart',
                                'rewind',
                                'play',
                                'fast-forward',
                                'progress',
                                'current-time',
                                'duration',
                                'mute',
                                'volume',
                                'settings',
                                'fullscreen',
                            ],
                            settings: ['quality', 'speed'],
                            speed: { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 2] },
                        });
                    
                    // Wait for player to be ready before marking as initialized
                    this.player.on('ready', () => {
                        this.initialized = true;
                    });
                    
                    // Only keep error logging for production debugging
                    this.player.on('error', (event) => {
                        console.error('Video player error:', event);
                    });

                    // Set initial position when metadata is loaded
                    if (initialPosition > 0) {
                        const setPosition = () => {
                            try {
                                if (this.videoElement.readyState >= 2) {
                                    this.videoElement.currentTime = initialPosition;
                                }
                            } catch (error) {
                                console.warn('Could not set initial position:', error);
                            }
                        };
                        
                        if (this.videoElement.readyState >= 2) {
                            setPosition();
                        } else {
                            this.videoElement.addEventListener('loadedmetadata', setPosition, { once: true });
                        }
                    }
                    
                    // Track progress
                    let lastUpdate = 0;
                    this.videoElement.addEventListener('timeupdate', () => {
                        if (!this.videoElement) return;
                        
                        const currentTime = this.videoElement.currentTime;
                        const duration = this.videoElement.duration;
                        
                        // Skip if duration is not valid
                        if (!duration || !isFinite(duration) || duration <= 0) {
                            return;
                        }
                        
                        const percentage = Math.round((currentTime / duration) * 100);
                        
                        // Update every 5 seconds or on pause/end
                        const now = Date.now();
                        if (now - lastUpdate > 5000 || this.videoElement.paused || this.videoElement.ended) {
                            if (percentage >= 0 && percentage <= 100) {
                                this.updateProgress(percentage, Math.round(currentTime));
                                lastUpdate = now;
                            }
                        }
                    });

                    // Handle video end
                    this.videoElement.addEventListener('ended', () => {
                        if (!this.videoElement) return;
                        
                        const duration = this.videoElement.duration;
                        if (duration && isFinite(duration) && duration > 0) {
                            this.updateProgress(100, Math.round(duration));
                        }
                    });
                } catch (error) {
                    console.error('Error creating video player:', error);
                    this.initialized = false;
                }
                }); // requestAnimationFrame
            });
        },

        updateProgress(percentage, position) {
            @this.call('updateProgress', percentage, position);
        },
        
        destroy() {
            // Destroy Plyr instance
            if (this.player) {
                try {
                    this.player.destroy();
                } catch (e) {
                    console.warn('Error destroying player:', e);
                }
                this.player = null;
            }
            
            // Clean up video element
            if (this.videoElement) {
                try {
                    // Pause and remove sources to release media resources
                    this.videoElement.pause();
                    this.videoElement.removeAttribute('src');
                    
                    // Remove all source elements
                    const sources = this.videoElement.querySelectorAll('source');
                    sources.forEach(source => source.remove());
                    
                    // Load empty to release resources
                    this.videoElement.load();
                    
                    // Clear plyr reference and properties
                    delete this.videoElement.plyr;
                    
                    // Remove Plyr-specific properties to prevent redefine errors
                    ['quality', 'speed', 'loop', 'language'].forEach(prop => {
                        try {
                            delete this.videoElement[prop];
                        } catch (e) {
                            // Property might be non-configurable, ignore
                        }
                    });
                } catch (e) {
                    console.warn('Error cleaning up video element:', e);
                }
                this.videoElement = null;
            }
            
            this.initialized = false;
        }
    }
}
</script>
@endpush
