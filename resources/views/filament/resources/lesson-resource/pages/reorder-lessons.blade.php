<x-filament-panels::page>
    <style>
        .sortable-ghost {
            opacity: 0.4;
            background-color: rgb(239 246 255) !important;
        }
        .dark .sortable-ghost {
            background-color: rgb(59 130 246 / 0.1) !important;
        }
        .sortable-drag {
            opacity: 0.5;
        }
    </style>
    
    <div class="space-y-6">
        <!-- Course Filter -->
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                Filter by Course
                            </span>
                        </label>
                        <select 
                            wire:model.live="selectedCourse"
                            class="fi-input block w-full rounded-lg border-none bg-white py-1.5 pe-3 ps-3 text-base text-gray-950 shadow-sm ring-1 ring-gray-950/10 transition duration-75 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:focus:ring-primary-500 sm:text-sm sm:leading-6"
                        >
                            <option value="">All Courses</option>
                            @foreach($courses as $id => $title)
                                <option value="{{ $id }}">{{ $title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <div class="rounded-lg bg-primary-50 px-4 py-3 dark:bg-primary-500/10">
                            <p class="text-sm text-primary-600 dark:text-primary-400">
                                <strong>{{ count($lessons) }}</strong> lessons found. Drag and drop to reorder.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lessons Table with Drag & Drop -->
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6">
                @if(count($lessons) > 0)
                    <div class="overflow-hidden overflow-x-auto">
                        <table class="w-full table-auto divide-y divide-gray-200 dark:divide-white/10">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-white/5">
                                    <th class="w-12 px-3 py-3.5 text-center">
                                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-950 dark:text-white">
                                            Drag
                                        </span>
                                    </th>
                                    <th class="w-20 px-3 py-3.5 text-center">
                                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-950 dark:text-white">
                                            Order
                                        </span>
                                    </th>
                                    <th class="px-3 py-3.5 text-left">
                                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-950 dark:text-white">
                                            Course
                                        </span>
                                    </th>
                                    <th class="px-3 py-3.5 text-left">
                                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-950 dark:text-white">
                                            Lesson Title
                                        </span>
                                    </th>
                                    <th class="w-32 px-3 py-3.5 text-center">
                                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-950 dark:text-white">
                                            Duration
                                        </span>
                                    </th>
                                    <th class="w-32 px-3 py-3.5 text-center">
                                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-950 dark:text-white">
                                            Published
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="sortable-lessons" class="divide-y divide-gray-200 dark:divide-white/10">
                                @foreach($lessons as $lesson)
                                    <tr data-id="{{ $lesson['id'] }}" class="hover:bg-gray-50 dark:hover:bg-white/5 transition cursor-move">
                                        <td class="px-3 py-4 text-center">
                                            <div class="flex justify-center">
                                                <svg class="h-5 w-5 text-gray-400 drag-handle cursor-grab active:cursor-grabbing" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                                </svg>
                                            </div>
                                        </td>
                                        <td class="px-3 py-4 text-center">
                                            <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20">
                                                {{ $lesson['order'] }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-4">
                                            <span class="text-sm text-gray-950 dark:text-white">
                                                {{ $lesson['course'] }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-4">
                                            <span class="text-sm font-medium text-gray-950 dark:text-white">
                                                {{ $lesson['title'] }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-4 text-center">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $lesson['duration'] }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-4 text-center">
                                            @if($lesson['is_published'])
                                                <svg class="inline-block h-6 w-6 text-success-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                            @else
                                                <svg class="inline-block h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776" />
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No lessons found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new lesson.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initSortable();
        });

        function initSortable() {
            const el = document.getElementById('sortable-lessons');
            if (!el || typeof Sortable === 'undefined') return;
            
            if (el.sortableInstance) {
                el.sortableInstance.destroy();
            }
            
            el.sortableInstance = new Sortable(el, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    const orderedIds = Array.from(el.querySelectorAll('tr[data-id]')).map(tr => tr.dataset.id);
                    
                    // Use Livewire to call the updateOrder method
                    const component = window.Livewire.find(el.closest('[wire\\:id]').getAttribute('wire:id'));
                    if (component) {
                        component.call('updateOrder', orderedIds);
                    }
                }
            });
        }

        // Reinitialize after Livewire updates
        document.addEventListener('livewire:load', initSortable);
        document.addEventListener('livewire:update', initSortable);
    </script>
</x-filament-panels::page>
