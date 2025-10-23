<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Video Player -->
        <div class="lg:col-span-3">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden transition-colors duration-200">
                @if($canWatchLesson)
                    <!-- Fixed aspect ratio container to prevent layout shift -->
                    <div class="relative aspect-video bg-black" 
                         wire:key="video-container-{{ $currentLesson->id }}"
                         wire:ignore
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
                <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                            {{ $currentLesson->title }}
                        </h1>
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-3">
                            <span class="mr-4">Duration: {{ $currentLesson->formatted_duration }}</span>
                            @if($currentLesson->is_free_preview)
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">
                                    Free Preview
                                </span>
                            @endif
                        </div>
                        
                        @if($currentLesson->description)
                            <div class="prose prose-sm max-w-none text-gray-600 dark:text-gray-300">
                                <p>{{ $currentLesson->description }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Mark as Complete Button with Alpine.js Confirmation Modal -->
                    @if($canWatchLesson && $isEnrolled && !$progress['is_completed'])
                        <div x-data="{ showCompletionModal: false }" class="mb-4">
                            <button 
                                @click="showCompletionModal = true"
                                class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Mark as Complete
                            </button>
                            
                            <!-- Confirmation Modal -->
                            <div 
                                x-show="showCompletionModal"
                                x-cloak
                                class="fixed inset-0 z-50 overflow-y-auto"
                                @keydown.escape.window="showCompletionModal = false"
                                style="display: none;"
                            >
                                <!-- Backdrop -->
                                <div 
                                    class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                                    @click="showCompletionModal = false"
                                    x-transition:enter="ease-out duration-300"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in duration-200"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                ></div>
                                
                                <!-- Modal Content -->
                                <div class="flex items-center justify-center min-h-screen p-4">
                                    <div 
                                        x-show="showCompletionModal"
                                        x-transition:enter="ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform scale-90"
                                        x-transition:enter-end="opacity-100 transform scale-100"
                                        x-transition:leave="ease-in duration-200"
                                        x-transition:leave-start="opacity-100 transform scale-100"
                                        x-transition:leave-end="opacity-0 transform scale-90"
                                        class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6 relative z-10 transition-colors duration-200"
                                        @click.stop
                                    >
                                        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-green-100 rounded-full mb-4">
                                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white text-center mb-2">
                                            Mark Lesson as Complete?
                                        </h3>
                                        <p class="text-gray-600 dark:text-gray-300 text-center mb-6">
                                            Are you sure you want to mark <strong>"{{ $currentLesson->title }}"</strong> as completed? This will update your course progress.
                                        </p>
                                        
                                        <div class="flex justify-end space-x-3">
                                            <button 
                                                @click="showCompletionModal = false"
                                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors"
                                            >
                                                Cancel
                                            </button>
                                            <button 
                                                @click="showCompletionModal = false; $wire.markAsComplete()"
                                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
                                            >
                                                Confirm
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

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
                    <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button 
                            wire:click="previousLesson"
                            @disabled(!$this->hasPreviousLesson())
                            class="inline-flex items-center px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden sticky top-4 transition-colors duration-200">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-gray-700 dark:to-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Course Content</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ $course->publishedLessons->count() }} lessons
                    </p>
                    
                    @if($isEnrolled && Auth::check())
                        <div class="mt-3">
                            <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                                <span>Progress</span>
                                <span class="font-medium">{{ $this->completedLessons }}/{{ $this->totalLessons }} completed</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: {{ $this->progressPercentage }}%"></div>
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
                                    ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-900 dark:text-indigo-100 border border-indigo-200 dark:border-indigo-700' 
                                    : 'hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' 
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
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
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
    
    /* Alpine.js cloak - hide elements before Alpine initializes */
    [x-cloak] { 
        display: none !important; 
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
            if (this.player || this.initialized) {
                return;
            }
            
            // Mark as initializing to prevent double init
            this.initialized = true;
            
            // Wait for DOM to be fully ready
            setTimeout(() => {
                this.videoElement = this.$el.querySelector('video');
                
                if (!this.videoElement) {
                    console.error('Video element not found');
                    return;
                }
                
                if (typeof Plyr === 'undefined') {
                    console.error('Plyr is not loaded');
                    return;
                }
                
                // Destroy any existing Plyr instance
                if (this.videoElement.plyr) {
                    this.videoElement.plyr.destroy();
                }
                
                try {
                    // Initialize Plyr
                    this.player = new Plyr(this.videoElement, {
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
                
                    this.initialized = true;
                
                    // Set initial position when ready
                    this.player.on('ready', (event) => {
                        // Wait a bit for video to fully load
                        setTimeout(() => {
                            if (initialPosition > 0 && this.player.duration > 0) {
                                this.player.currentTime = initialPosition;
                            }
                        }, 100);
                    });
                    
                    // Track progress
                    let lastUpdate = 0;
                    this.player.on('timeupdate', () => {
                        const currentTime = this.player.currentTime;
                        const duration = this.player.duration;
                        
                        if (!duration || duration <= 0) return;
                        
                        const percentage = Math.round((currentTime / duration) * 100);
                        const now = Date.now();
                        
                        // Save every 5 seconds while playing
                        if (now - lastUpdate > 5000) {
                            if (percentage >= 0 && percentage <= 100) {
                                this.updateProgress(percentage, Math.round(currentTime));
                                lastUpdate = now;
                            }
                        }
                    });
                    
                    // Save on pause
                    this.player.on('pause', () => {
                        const currentTime = this.player.currentTime;
                        const duration = this.player.duration;
                        if (duration > 0) {
                            const percentage = Math.round((currentTime / duration) * 100);
                            this.updateProgress(percentage, Math.round(currentTime));
                        }
                    });

                    // Handle video end
                    this.player.on('ended', () => {
                        const duration = this.player.duration;
                        if (duration > 0) {
                            this.updateProgress(100, Math.round(duration));
                        }
                    });
                } catch (error) {
                    console.error('Error creating video player:', error);
                }
            }, 100);
        },

        updateProgress(percentage, position) {
            // Use $wire for Livewire 3 compatibility
            if (typeof $wire !== 'undefined') {
                $wire.call('updateProgress', percentage, position);
            } else if (typeof Livewire !== 'undefined') {
                Livewire.find(this.$el.closest('[wire\\:id]').getAttribute('wire:id')).call('updateProgress', percentage, position);
            } else {
                console.error('Livewire not available for progress update');
            }
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
