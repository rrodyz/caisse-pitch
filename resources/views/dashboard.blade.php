<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between pr-4">
            <h2 class="font-semibold text-xl text-night-50 leading-tight">
                Tableau de bord
            </h2>
            <span class="hidden md:block text-sm text-night-300 capitalize whitespace-nowrap">{{ now()->isoFormat('dddd D MMMM YYYY') }}</span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:dashboard.dashboard-widget />
        </div>
    </div>
</x-app-layout>
