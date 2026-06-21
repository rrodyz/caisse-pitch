<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css?family=inter:400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { background: #05050c; }
        .pos-grid { height: calc(100vh - 52px); }
    </style>
</head>
<body class="font-sans antialiased">
    {{-- Topbar POS --}}
    <div class="h-[52px] bg-night-950 border-b border-white/5 flex items-center justify-between px-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-1.5 text-night-300 hover:text-night-100 text-sm transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Retour
            </a>
            <span class="text-night-600">|</span>
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded bg-gradient-to-br from-gold-400 to-gold-600 flex items-center justify-center">
                    <svg class="h-3.5 w-3.5 text-night-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="text-white font-bold text-sm">{{ config('app.name') }}</span>
                <span class="text-night-300 text-sm">— POS</span>
            </div>
        </div>
        <div class="text-gold-400 text-sm font-mono font-semibold tabular-nums" id="pos-clock"></div>
        <script>
            function tick(){var e=document.getElementById('pos-clock');if(e)e.textContent=new Date().toLocaleTimeString('fr-FR');}
            tick();setInterval(tick,1000);
        </script>
        <div class="flex items-center gap-2 text-night-200 text-sm">
            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-neon-500 to-neon-700 flex items-center justify-center text-white text-xs font-bold">
                {{ strtoupper(substr(auth()->user()->first_name ?? '', 0, 1) . substr(auth()->user()->last_name ?? '', 0, 1)) }}
            </div>
            <span class="hidden sm:block">{{ auth()->user()->full_name }}</span>
        </div>
    </div>

    <div class="pos-grid">
        @livewire('sales.pos-terminal')
    </div>

    @livewireScripts
</body>
</html>
