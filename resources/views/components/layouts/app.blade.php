<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' · ' : '' }}{{ \App\Support\AppSettings::appName() }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased app-shell">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen">
        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
             class="fixed inset-0 z-30 bg-black/50 lg:hidden" style="display:none"></div>

        @include('partials.sidebar')

        <div class="lg:pl-64">
            {{-- Topbar --}}
            <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-neutral-200/80 bg-white/80 px-4 backdrop-blur-md dark:border-neutral-800 dark:bg-neutral-950/80">
                <button @click="sidebarOpen = true" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 lg:hidden">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <div class="flex-1"></div>

                <div class="flex items-center gap-3">
                    <span class="hidden text-sm text-slate-500 dark:text-slate-400 sm:block">{{ auth()->user()->name }}</span>
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex h-9 w-9 items-center justify-center rounded-full bg-neutral-900 text-sm font-semibold text-white dark:bg-white dark:text-neutral-900">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition style="display:none"
                             class="absolute right-0 mt-2 w-48 rounded-lg border border-slate-200 bg-white py-1 shadow-lg dark:border-slate-700 dark:bg-slate-800">
                            <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-700">Profil</a>
                            <a href="{{ route('settings.edit') }}" class="block px-4 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-700">Podešavanja</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-slate-100 dark:hover:bg-slate-700">Odjava</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Flash messages --}}
            <div class="px-4 pt-4 sm:px-6 lg:px-8" x-data="{ show: true }" x-show="show">
                @if (session('success'))
                    <div class="mb-2 flex items-center justify-between rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-900/30 dark:text-green-300">
                        <span>{{ session('success') }}</span>
                        <button @click="show = false" class="text-green-600">&times;</button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-2 flex items-center justify-between rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-900/30 dark:text-red-300">
                        <span>{{ session('error') }}</span>
                        <button @click="show = false" class="text-red-600">&times;</button>
                    </div>
                @endif
            </div>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>
        </div>
    </div>
    @livewireScripts
</body>
</html>
