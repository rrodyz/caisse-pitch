<div class="p-6">
    <div class="flex items-center justify-between mb-4 gap-3">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Nom, téléphone, IFU..."
            class="border-white/10 rounded-md shadow-sm text-sm w-64 focus:ring-neon-500/30 focus:border-neon-500">
        @can('create-suppliers')
            <button wire:click="openCreate"
                class="inline-flex items-center px-4 py-2 bg-neon-600 text-white text-sm font-semibold rounded-lg hover:bg-neon-500">
                + Nouveau fournisseur
            </button>
        @endcan
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/5 text-sm">
            <thead class="bg-night-700">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Nom</th>
                    <th class="px-4 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Contact</th>
                    <th class="px-4 py-3 text-left font-medium text-night-200 uppercase tracking-wider">IFU</th>
                    <th class="px-4 py-3 text-center font-medium text-night-200 uppercase tracking-wider">Achats</th>
                    <th class="px-4 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Statut</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse ($suppliers as $s)
                    <tr wire:key="sup-{{ $s->id }}" class="{{ $s->is_active ? '' : 'opacity-50' }}">
                        <td class="px-4 py-3">
                            <div class="font-medium text-night-50">{{ $s->name }}</div>
                            @if($s->address)
                                <div class="text-xs text-night-300">{{ Str::limit($s->address, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-night-200">
                            @if($s->contact_name) <div>{{ $s->contact_name }}</div> @endif
                            @if($s->phone) <div class="text-xs text-night-300">{{ $s->phone }}</div> @endif
                            @if($s->email) <div class="text-xs text-night-300">{{ $s->email }}</div> @endif
                        </td>
                        <td class="px-4 py-3 text-night-300 font-mono text-xs">{{ $s->ifu ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('purchases.index') }}?supplier={{ $s->id }}"
                                class="text-neon-400 hover:underline">{{ $s->purchases_count }}</a>
                        </td>
                        <td class="px-4 py-3">
                            <button wire:click="toggleActive({{ $s->id }})"
                                class="inline-flex items-center px-2 py-1 text-xs rounded-full font-medium
                                    {{ $s->is_active ? 'bg-emerald-500/15 text-emerald-300' : 'bg-night-700 text-night-200' }}">
                                {{ $s->is_active ? 'Actif' : 'Inactif' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            @can('edit-suppliers')
                                <button wire:click="openEdit({{ $s->id }})" class="text-neon-400 hover:text-neon-200 text-xs font-medium">Modifier</button>
                            @endcan
                            @can('delete-suppliers')
                                <button wire:click="delete({{ $s->id }})"
                                    wire:confirm="Supprimer ce fournisseur ?"
                                    class="text-red-400 hover:text-red-400 text-xs font-medium">Supprimer</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-night-300">Aucun fournisseur.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $suppliers->links() }}</div>

    @if ($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
        <div class="bg-night-800 rounded-xl border border-white/8 shadow-2xl w-full max-w-lg p-6">
            <h3 class="text-lg font-semibold mb-4">{{ $editingId ? 'Modifier' : 'Nouveau' }} fournisseur</h3>
            <form wire:submit="save" class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-night-100">Nom *</label>
                        <input wire:model="name" type="text" autofocus
                            class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-night-100">Téléphone</label>
                        <input wire:model="phone" type="text"
                            class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-night-100">Email</label>
                        <input wire:model="email" type="email"
                            class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-night-100">IFU</label>
                        <input wire:model="ifu" type="text"
                            class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-night-100">Contact principal</label>
                        <input wire:model="contact_name" type="text"
                            class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-night-100">Adresse</label>
                        <textarea wire:model="address" rows="2"
                            class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500"></textarea>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-night-100">Notes</label>
                        <textarea wire:model="notes" rows="2"
                            class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500"></textarea>
                    </div>
                    <div class="col-span-2 flex items-center gap-2">
                        <input wire:model="is_active" type="checkbox" id="sup_active" class="rounded border-white/10 text-neon-400">
                        <label for="sup_active" class="text-sm text-night-100">Fournisseur actif</label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showModal', false)"
                        class="px-4 py-2 text-sm text-night-200 border border-white/10 rounded-md hover:bg-night-700">Annuler</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-neon-600 rounded-lg hover:bg-neon-500">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
