<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="color-scheme" content="light">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                background: #0d0818;
                background-image:
                    radial-gradient(ellipse 80% 60% at 15% 10%, rgba(91,33,182,0.45) 0%, transparent 65%),
                    radial-gradient(ellipse 60% 55% at 85% 90%, rgba(67,56,202,0.40) 0%, transparent 65%),
                    radial-gradient(ellipse 50% 40% at 50% 50%, rgba(109,40,217,0.15) 0%, transparent 70%);
            }
            /* Force light mode on the login card — prevents Chrome dark-mode from overriding inputs */
            .login-card {
                color-scheme: light;
            }
            /* Override Chrome autofill + auto-dark-mode on inputs */
            .login-card input[type="email"],
            .login-card input[type="password"],
            .login-card input[type="text"] {
                background-color: #f9fafb !important;
                color: #1f2937 !important;
                border-color: #e5e7eb !important;
            }
            input:-webkit-autofill,
            input:-webkit-autofill:hover,
            input:-webkit-autofill:focus {
                -webkit-box-shadow: 0 0 0 1000px #f9fafb inset !important;
                -webkit-text-fill-color: #1f2937 !important;
                caret-color: #1f2937;
            }
        </style>
    </head>
    <body class="font-sans antialiased min-h-screen flex items-center justify-center p-4">

        <div class="w-full max-w-sm">

            {{-- Avatar / logo qui déborde au-dessus de la carte --}}
            <div class="flex justify-center mb-0">
                <div class="relative w-24 h-24 rounded-full bg-white shadow-2xl shadow-black/40 flex items-center justify-center border-4 border-white z-10 -mb-12">
                    <div class="w-[72px] h-[72px] rounded-full bg-gradient-to-br from-neon-400 to-neon-700 flex items-center justify-center shadow-inner">
                        <svg class="h-9 w-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                  d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Carte blanche --}}
            <div class="login-card bg-white rounded-3xl shadow-2xl shadow-black/30 px-8"
                 style="padding-top:4rem;padding-bottom:2rem">

                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">{{ __('Connexion') }}</h1>
                    <p class="text-sm text-gray-400 mt-1">{{ config('app.name') }} — Espace staff</p>
                </div>

                {{ $slot }}

            </div>

            <p class="text-center mt-5 text-xs text-white/30">© {{ date('Y') }} {{ config('app.name') }}</p>
        </div>

    </body>
</html>
