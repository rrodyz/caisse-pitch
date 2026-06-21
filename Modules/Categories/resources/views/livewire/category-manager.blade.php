<div class="p-6">

    {{-- Barre d'outils --}}
    <div class="flex items-center justify-between mb-4 gap-3">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher..."
            class="border-white/10 rounded-md shadow-sm text-sm w-64 focus:ring-neon-500/30 focus:border-neon-500">
        @can('create-categories')
            <button wire:click="openCreate"
                class="inline-flex items-center px-4 py-2 bg-neon-600 text-white text-sm font-semibold rounded-lg hover:bg-neon-500">
                + Nouvelle catégorie
            </button>
        @endcan
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/5 text-sm">
            <thead class="bg-night-700">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Couleur</th>
                    <th class="px-4 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Nom</th>
                    <th class="px-4 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Ordre POS</th>
                    <th class="px-4 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Produits</th>
                    <th class="px-4 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Statut</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse ($categories as $cat)
                    <tr wire:key="cat-{{ $cat->id }}" class="{{ $cat->is_active ? '' : 'opacity-50' }}">
                        <td class="px-4 py-3">
                            <div class="w-7 h-7 rounded-full border border-white/8" style="background-color: {{ $cat->color }}"></div>
                        </td>
                        <td class="px-4 py-3 font-medium text-night-50">
                            {{ $cat->name }}
                            @if($cat->description)
                                <div class="text-xs text-night-300">{{ Str::limit($cat->description, 60) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-night-200">{{ $cat->pos_order }}</td>
                        <td class="px-4 py-3 text-night-200">{{ $cat->products_count }}</td>
                        <td class="px-4 py-3">
                            <button wire:click="toggleActive({{ $cat->id }})"
                                class="inline-flex items-center px-2 py-1 text-xs rounded-full font-medium
                                    {{ $cat->is_active ? 'bg-emerald-500/15 text-emerald-300' : 'bg-night-700 text-night-200' }}">
                                {{ $cat->is_active ? 'Actif' : 'Inactif' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            @can('edit-categories')
                                <button wire:click="openEdit({{ $cat->id }})" class="text-neon-400 hover:text-neon-200 text-xs font-medium">Modifier</button>
                            @endcan
                            @can('delete-categories')
                                <button wire:click="delete({{ $cat->id }})"
                                    wire:confirm="Supprimer cette catégorie ?"
                                    class="text-red-400 hover:text-red-400 text-xs font-medium">Supprimer</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-night-300">Aucune catégorie trouvée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $categories->links() }}</div>

    {{-- Modal create/edit --}}
    @if ($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm" wire:key="modal">
        <div class="bg-night-800 rounded-xl border border-white/8 shadow-2xl w-full max-w-md p-6" @click.stop>
            <h3 class="text-lg font-semibold mb-4">{{ $editingId ? 'Modifier' : 'Nouvelle' }} catégorie</h3>

            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-night-100">Nom *</label>
                    <input wire:model="name" type="text" autofocus
                        class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-night-100">Description</label>
                    <textarea wire:model="description" rows="2"
                        class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-night-100">Couleur POS</label>
                        <div class="flex items-center gap-2 mt-1">
                            <input wire:model.live="color" type="color" class="h-9 w-14 cursor-pointer rounded border-white/10">
                            <span class="text-xs text-night-200">{{ $color }}</span>
                        </div>
                        @error('color') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-night-100">Ordre d'affichage</label>
                        <input wire:model="pos_order" type="number" min="0"
                            class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input wire:model="is_active" type="checkbox" id="is_active_cat" class="rounded border-white/10 text-neon-400">
                    <label for="is_active_cat" class="text-sm text-night-100">Catégorie active</label>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showModal', false)"
                        class="px-4 py-2 text-sm text-night-200 border border-white/10 rounded-md hover:bg-night-700">Annuler</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-neon-600 rounded-lg hover:bg-neon-500"
                        wire:loading.attr="disabled">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
