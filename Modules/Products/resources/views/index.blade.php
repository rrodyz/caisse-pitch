<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-night-50 leading-tight">Produits</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-emerald-500/10 border border-emerald-500/25 text-emerald-300 rounded">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-500/10 border border-red-500/25 text-red-300 rounded">{{ session('error') }}</div>
            @endif
            <div class="bg-night-800 border border-white/5 rounded-xl">
                <livewire:products.product-list />
            </div>
        </div>
    </div>
</x-app-layout>
