<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@isset($title){{ $title }} — @endisset{{ config('app.name', 'Caisse') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css?family=inter:400,500,600,700&display=swap" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-night-900 text-night-50">

<div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 z-20 bg-black/70 lg:hidden"
         style="display: none;"></div>

    {{-- Sidebar --}}
    @include('layouts.sidebar')

    {{-- Main area --}}
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

        {{-- Top bar --}}
        <div class="bg-night-800 border-b border-white/5 flex items-center justify-between px-4 sm:px-6 h-14 flex-shrink-0">

            {{-- Mobile hamburger --}}
            <button @click="sidebarOpen = true"
                    class="lg:hidden p-1.5 rounded-md text-night-200 hover:bg-night-700 hover:text-night-50 transition-colors">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Page header slot --}}
            <div class="flex-1 ml-3 lg:ml-0 min-w-0">
                @isset($header)
                    {{ $header }}
                @endisset
            </div>

            {{-- Right: clock + user --}}
            <div class="flex items-center gap-3">
                {{-- Live clock --}}
                <div class="hidden sm:block text-night-200 text-xs font-mono" id="topbar-clock"></div>
                <script>
                    function tickClock(){var e=document.getElementById('topbar-clock');if(e)e.textContent=new Date().toLocaleTimeString('fr-FR');}
                    tickClock();setInterval(tickClock,1000);
                </script>

                {{-- User dropdown --}}
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 py-1.5 px-2.5 rounded-lg text-sm text-night-200 hover:bg-night-700 hover:text-night-50 focus:outline-none transition-colors">
                            <div class="h-7 w-7 rounded-full bg-gradient-to-br from-neon-500 to-neon-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr(Auth::user()->first_name ?? '', 0, 1) . substr(Auth::user()->last_name ?? '', 0, 1)) }}
                            </div>
                            <span class="hidden sm:block font-medium truncate max-w-[120px]">{{ Auth::user()->first_name }}</span>
                            <svg class="h-4 w-4 text-night-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-white/5">
                            <p class="text-sm font-medium text-night-50">{{ Auth::user()->full_name }}</p>
                            <p class="text-xs text-night-200 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <x-dropdown-link :href="route('profile.edit')">
                            <svg class="inline h-4 w-4 mr-2 text-night-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Mon profil
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                <svg class="inline h-4 w-4 mr-2 text-night-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Déconnexion
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto bg-night-900">
            {{ $slot }}
        </main>
    </div>
</div>

</body>
</html>
