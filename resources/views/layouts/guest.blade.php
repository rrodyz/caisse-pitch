<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                background: #0d0818;
                background-image:
                    radial-gradient(ellipse 80% 60% at 15% 10%, rgba(91,33,182,0.35) 0%, transparent 70%),
                    radial-gradient(ellipse 60% 50% at 85% 85%, rgba(67,56,202,0.30) 0%, transparent 70%),
                    radial-gradient(ellipse 40% 40% at 50% 100%, rgba(212,175,55,0.08) 0%, transparent 60%);
            }
        </style>
    </head>
    <body class="font-sans antialiased text-night-50 min-h-screen">

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">

            {{-- Logo --}}
            <div class="mb-8">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-gold-300 to-gold-600 flex items-center justify-center shadow-lg shadow-gold-500/30">
                        <svg class="h-6 w-6 text-night-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold text-night-50 tracking-tight">{{ config('app.name') }}</span>
                </div>
            </div>

            <div class="w-full sm:max-w-md px-8 py-8 bg-white/5 backdrop-blur-xl border border-white/10 shadow-2xl shadow-black/50 sm:rounded-2xl">
                {{ $slot }}
            </div>

            <p class="mt-6 text-xs text-night-300/50">{{ config('app.name') }} &copy; {{ date('Y') }}</p>
        </div>
    </body>
</html>
