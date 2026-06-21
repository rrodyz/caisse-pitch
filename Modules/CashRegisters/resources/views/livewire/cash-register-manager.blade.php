<div class="p-6">

    <div class="flex justify-between items-center mb-4">
        <h3 class="text-base font-semibold text-night-50">Caisses enregistreuses</h3>
        @can('manage-cash-registers')
            <button wire:click="openCreate"
                class="px-4 py-2 bg-neon-600 text-white text-sm font-semibold rounded-lg hover:bg-neon-500">
                + Nouvelle caisse
            </button>
        @endcan
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/5 text-sm">
            <thead class="bg-night-700">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-night-200">Nom</th>
                    <th class="px-4 py-3 text-left font-medium text-night-200">Emplacement</th>
                    <th class="px-4 py-3 text-center font-medium text-night-200">Sessions</th>
                    <th class="px-4 py-3 text-left font-medium text-night-200">Session active</th>
                    <th class="px-4 py-3 text-left font-medium text-night-200">Statut</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse ($registers as $reg)
                    <tr wire:key="reg-{{ $reg->id }}">
                        <td class="px-4 py-3 font-medium text-night-50">{{ $reg->name }}</td>
                        <td class="px-4 py-3 text-night-200">{{ $reg->location ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-night-200">{{ $reg->sessions_count }}</td>
                        <td class="px-4 py-3 text-xs text-night-200">
                            @if ($reg->activeSession)
                                <span class="text-emerald-400 font-medium">Ouverte</span>
                                par {{ $reg->activeSession->openedBy?->full_name }}
                                depuis {{ $reg->activeSession->opened_at->diffForHumans() }}
                            @else
                                <span class="text-night-300">Aucune</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <button wire:click="toggleActive({{ $reg->id }})"
                                class="inline-flex px-2 py-0.5 text-xs rounded-full font-medium
                                    {{ $reg->is_active ? 'bg-emerald-500/15 text-emerald-300' : 'bg-night-700 text-night-300' }}">
                                {{ $reg->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @can('manage-cash-registers')
                                <button wire:click="openEdit({{ $reg->id }})"
                                    class="text-neon-400 hover:text-neon-200 text-xs font-medium">Modifier</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-night-300">Aucune caisse.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $registers->links() }}</div>

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-night-800 rounded-xl shadow-2xl w-full max-w-md mx-4 p-6">
                <h3 class="text-lg font-semibold text-night-50 mb-4">
                    {{ $editingId ? 'Modifier la caisse' : 'Nouvelle caisse' }}
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-night-200 mb-1">Nom *</label>
                        <input wire:model="name" type="text" placeholder="Ex: Caisse Bar Principal"
                            class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-night-200 mb-1">Emplacement</label>
                        <input wire:model="location" type="text" placeholder="Ex: Bar VIP, Terrasse..."
                            class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                    </div>
                    <div class="flex items-center gap-2">
                        <input wire:model="is_active" type="checkbox" id="cr_active"
                            class="rounded border-white/10 text-neon-400">
                        <label for="cr_active" class="text-sm text-night-100">Caisse active</label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-white/8">
                    <button wire:click="$set('showModal',false)"
                        class="px-4 py-2 text-sm text-night-200 border border-white/10 rounded-md hover:bg-night-700">
                        Annuler
                    </button>
                    <button wire:click="save"
                        class="px-4 py-2 text-sm text-white bg-neon-600 rounded-lg hover:bg-neon-500">
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
