<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-night-50 leading-tight">Historique des ventes</h2>
            @can('create-sales')
                <a href="{{ route('pos.index') }}" class="px-4 py-2 bg-neon-600 text-white text-sm font-semibold rounded-lg hover:bg-neon-500">
                    → Ouvrir POS
                </a>
            @endcan
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @foreach(['success','error'] as $t)
                @if(session($t))
                    <div class="mb-4 p-4 rounded border {{ $t==='success' ? 'bg-emerald-500/10 border-emerald-500/25 text-emerald-300' : 'bg-red-500/10 border-red-500/25 text-red-300' }}">{{ session($t) }}</div>
                @endif
            @endforeach
            <div class="bg-night-800 border border-white/5 rounded-xl">
                <livewire:sales.sale-list />
            </div>
        </div>
    </div>
</x-app-layout>
