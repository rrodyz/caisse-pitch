<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchases.index') }}" class="text-night-300 hover:text-night-100">← Achats</a>
            <h2 class="font-semibold text-xl text-night-50 leading-tight">Nouvel achat</h2>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-night-800 border border-white/5 rounded-xl">
                <livewire:purchases.purchase-form />
            </div>
        </div>
    </div>
</x-app-layout>
