<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-night-50 leading-tight">Utilisateurs</h2>
            @can('manage-roles')
                <a href="{{ route('roles.index') }}"
                   class="text-sm text-neon-400 hover:text-neon-300 font-medium">
                    Gérer les rôles →
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @foreach (['success', 'error'] as $type)
                @if (session($type))
                    <div class="mb-4 p-4 rounded border {{ $type === 'success' ? 'bg-emerald-500/10 border-emerald-500/25 text-emerald-300' : 'bg-red-500/10 border-red-500/25 text-red-300' }}">
                        {{ session($type) }}
                    </div>
                @endif
            @endforeach
            <div class="bg-night-800 border border-white/5 rounded-xl">
                <livewire:users.user-manager />
            </div>
        </div>
    </div>
</x-app-layout>
