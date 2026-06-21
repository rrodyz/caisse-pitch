<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-night-50 leading-tight">Rapport de stock</h2>
            <a href="{{ route('reports.sales') }}" class="text-sm text-neon-400 hover:text-neon-400 font-medium">
                → Rapport ventes
            </a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-night-800 border border-white/5 rounded-xl">
                <livewire:reports.stock-report />
            </div>
        </div>
    </div>
</x-app-layout>
