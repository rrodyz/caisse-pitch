<div class="p-6">

    {{-- Barre d'outils --}}
    <div class="flex flex-wrap items-end gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher nom, email, username..."
            class="border-white/10 rounded-md shadow-sm text-sm w-64 focus:ring-neon-500/30 focus:border-neon-500">

        <select wire:model.live="filterRole"
            class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
            <option value="">Tous les rôles</option>
            @foreach ($roles as $role)
                <option value="{{ $role->name }}">{{ $role->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterStatus"
            class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
            <option value="">Tous statuts</option>
            <option value="1">Actifs</option>
            <option value="0">Inactifs</option>
        </select>

        @can('create-users')
            <button wire:click="openCreate"
                class="ml-auto inline-flex items-center px-4 py-2 bg-neon-600 text-white text-sm font-semibold rounded-lg hover:bg-neon-500">
                + Nouvel utilisateur
            </button>
        @endcan
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/5 text-sm">
            <thead class="bg-night-700">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Utilisateur</th>
                    <th class="px-4 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Contact</th>
                    <th class="px-4 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Rôle</th>
                    <th class="px-4 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Statut</th>
                    <th class="px-4 py-3 text-left font-medium text-night-200 uppercase tracking-wider">Dernière connexion</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse ($users as $user)
                    <tr wire:key="user-{{ $user->id }}" class="{{ $user->is_active ? '' : 'opacity-50' }}">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-full bg-neon-600/20 flex items-center justify-center text-neon-400 text-sm font-bold flex-shrink-0">
                                    {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium text-night-50">
                                        {{ $user->full_name }}
                                        @if ($user->id === auth()->id())
                                            <span class="ml-1 text-xs text-indigo-500 font-normal">(vous)</span>
                                        @endif
                                    </div>
                                    @if ($user->username)
                                        <div class="text-xs text-night-300">@{{ $user->username }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-night-200">
                            <div>{{ $user->email }}</div>
                            @if ($user->phone)
                                <div class="text-xs text-night-300">{{ $user->phone }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @foreach ($user->roles as $role)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-neon-500/15 text-neon-300">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </td>
                        <td class="px-4 py-3">
                            @can('edit-users')
                                <button wire:click="toggleActive({{ $user->id }})"
                                    @if ($user->id === auth()->id()) disabled title="Impossible de désactiver votre propre compte" @endif
                                    class="inline-flex items-center px-2 py-1 text-xs rounded-full font-medium cursor-pointer
                                        {{ $user->is_active ? 'bg-emerald-500/15 text-emerald-300' : 'bg-night-700 text-night-200' }}
                                        {{ $user->id === auth()->id() ? 'opacity-50 cursor-not-allowed' : '' }}">
                                    {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                </button>
                            @else
                                <span class="inline-flex items-center px-2 py-1 text-xs rounded-full font-medium
                                    {{ $user->is_active ? 'bg-emerald-500/15 text-emerald-300' : 'bg-night-700 text-night-200' }}">
                                    {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            @endcan
                        </td>
                        <td class="px-4 py-3 text-night-300 text-xs">
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais' }}
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            @can('edit-users')
                                <button wire:click="openEdit({{ $user->id }})"
                                    class="text-neon-400 hover:text-neon-200 text-xs font-medium">
                                    Modifier
                                </button>
                            @endcan
                            @can('delete-users')
                                @if ($user->id !== auth()->id())
                                    <button wire:click="delete({{ $user->id }})"
                                        wire:confirm="Supprimer cet utilisateur ?"
                                        class="text-red-400 hover:text-red-400 text-xs font-medium">
                                        Supprimer
                                    </button>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-night-300">Aucun utilisateur trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>

    {{-- Modal create / edit --}}
    @if ($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
        <div class="bg-night-800 rounded-xl border border-white/8 shadow-2xl w-full max-w-lg p-6" @click.stop>
            <h3 class="text-lg font-semibold mb-5">
                {{ $editingId ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}
            </h3>

            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-night-100">Prénom *</label>
                        <input wire:model="first_name" type="text" autofocus
                            class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                        @error('first_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-night-100">Nom *</label>
                        <input wire:model="last_name" type="text"
                            class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                        @error('last_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-night-100">Email *</label>
                    <input wire:model="email" type="email"
                        class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-night-100">Nom d'utilisateur *</label>
                        <input wire:model="username" type="text"
                            class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                        @error('username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-night-100">Téléphone</label>
                        <input wire:model="phone" type="text"
                            class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                        @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-night-100">
                        Mot de passe {{ $editingId ? '(laisser vide pour ne pas changer)' : '*' }}
                    </label>
                    <input wire:model="password" type="password"
                        class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-night-100">Rôle *</label>
                        <select wire:model="role_name"
                            class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                            <option value="">— Choisir un rôle —</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex items-center gap-2 mt-6">
                        <input wire:model="is_active" type="checkbox" id="user_is_active"
                            class="rounded border-white/10 text-neon-400">
                        <label for="user_is_active" class="text-sm text-night-100">Compte actif</label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showModal', false)"
                        class="px-4 py-2 text-sm text-night-200 border border-white/10 rounded-md hover:bg-night-700">
                        Annuler
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-neon-600 rounded-lg hover:bg-neon-500"
                        wire:loading.attr="disabled">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
