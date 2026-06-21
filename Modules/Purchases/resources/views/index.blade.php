<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-night-50 leading-tight">Achats fournisseurs</h2>
            @can('create-purchases')
                <a href="{{ route('purchases.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-neon-600 text-white text-sm font-semibold rounded-lg hover:bg-neon-500">
                    + Nouvel achat
                </a>
            @endcan
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @foreach (['success', 'error'] as $type)
                @if (session($type))
                    <div class="mb-4 p-4 rounded {{ $type === 'success' ? 'bg-emerald-500/10 border-emerald-500/25 text-emerald-300' : 'bg-red-500/10 border-red-500/25 text-red-300' }} border">
                        {{ session($type) }}
                    </div>
                @endif
            @endforeach
            <div class="bg-night-800 border border-white/5 rounded-xl">
                <livewire:purchases.purchase-list />
            </div>
        </div>
    </div>
</x-app-layout>
