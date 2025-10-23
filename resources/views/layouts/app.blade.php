<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Mini LMS') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Plyr CSS -->
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    <script>
        // Prevent flash of unstyled content
        if (localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <div class="min-h-screen flex flex-col">
        @include('partials.header')

        <!-- Main Content -->
        <main class="flex-grow">
            @yield('content')
        </main>

        @include('partials.footer')
    </div>

    <!-- Plyr JS -->
    <script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
    
    @livewireScripts
    
    <!-- Dark Mode Alpine.js Component -->
    <script>
        document.addEventListener('livewire:init', () => {
            // Initialize dark mode after Livewire/Alpine loads
            Alpine.data('darkMode', () => ({
                darkMode: localStorage.getItem('darkMode') === 'true',
                
                init() {
                    // Watch for changes and save to localStorage
                    this.$watch('darkMode', value => {
                        localStorage.setItem('darkMode', value);
                        if (value) {
                            document.documentElement.classList.add('dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                        }
                    });
                    
                    // Apply initial state
                    if (this.darkMode) {
                        document.documentElement.classList.add('dark');
                    }
                },
                
                toggle() {
                    this.darkMode = !this.darkMode;
                }
            }));
        });
    </script>
    
    @stack('scripts')
</body>
</html>
