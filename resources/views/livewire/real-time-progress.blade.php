<div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Course Progress</h3>
        
        <div class="space-y-3">
            @foreach($course->publishedLessons as $lesson)
                @php
                    $lessonProgress = collect($progress)->firstWhere('lesson_id', $lesson->id);
                    $percentage = $lessonProgress['percentage'] ?? 0;
                    $isCompleted = $lessonProgress['is_completed'] ?? false;
                    $isInProgress = $lessonProgress['is_in_progress'] ?? false;
                @endphp
                
                <div class="flex items-center justify-between p-3 rounded-lg border
                    {{ $isCompleted ? 'bg-green-50 border-green-200' : 
                       ($isInProgress ? 'bg-blue-50 border-blue-200' : 'bg-gray-50 border-gray-200') }}">
                    
                    <div class="flex-1">
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-900">
                                {{ $lesson->title }}
                            </span>
                            
                            @if($isCompleted)
                                <svg class="w-4 h-4 text-green-500 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            @elseif($isInProgress)
                                <div class="w-4 h-4 border-2 border-blue-500 rounded-full ml-2 flex items-center justify-center">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="mt-2">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-300
                                    {{ $isCompleted ? 'bg-green-500' : 'bg-blue-500' }}"
                                     style="width: {{ $percentage }}%">
                                </div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $percentage }}% complete
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
