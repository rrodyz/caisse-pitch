<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-night-50 leading-tight">Journal d'activité</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-night-800 border border-white/5 rounded-xl">
                <livewire:users.activity-log-viewer />
            </div>
        </div>
    </div>
</x-app-layout>
