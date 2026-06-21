<div class="flex gap-0 min-h-[600px]">

    {{-- ── Colonne gauche : liste des rôles ── --}}
    <div class="w-72 flex-shrink-0 border-r border-white/8 bg-night-800 rounded-l-lg">
        <div class="p-4 border-b border-white/8 flex items-center justify-between">
            <h3 class="font-semibold text-night-50 text-sm">Rôles</h3>
            <button wire:click="openCreateRole"
                class="text-xs text-neon-400 hover:text-neon-200 font-medium">
                + Nouveau
            </button>
        </div>

        <ul class="divide-y divide-white/5">
            @foreach ($roles as $role)
                <li wire:key="role-{{ $role->name }}">
                    <button wire:click="selectRole('{{ $role->name }}')"
                        class="w-full text-left px-4 py-3 hover:bg-night-700 transition-colors
                            {{ $selectedRole === $role->name ? 'bg-neon-600/15 border-r-2 border-neon-500' : '' }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-sm font-medium text-night-50">{{ $role->name }}</span>
                                @if ($role->name === 'Administrateur')
                                    <span class="ml-1 text-xs text-amber-600">🔒</span>
                                @endif
                            </div>
                            <span class="text-xs text-night-300">{{ $role->users_count }} user(s)</span>
                        </div>
                        <div class="text-xs text-night-300 mt-0.5">
                            {{ $role->permissions->count() }} permission(s)
                        </div>
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- ── Colonne droite : permissions du rôle sélectionné ── --}}
    <div class="flex-1 bg-night-800 rounded-r-lg">
        @if ($selectedRole)
            <div class="p-5 border-b border-white/8 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-night-50">{{ $selectedRole }}</h3>
                    <p class="text-xs text-night-300 mt-0.5">
                        {{ count($rolePermissions) }} permission(s) active(s)
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    @if ($selectedRole !== 'Administrateur')
                        <button wire:click="deleteRole('{{ $selectedRole }}')"
                            wire:confirm="Supprimer le rôle «{{ $selectedRole }}» ?"
                            class="text-xs text-red-400 hover:text-red-400 font-medium">
                            Supprimer ce rôle
                        </button>
                        <button wire:click="savePermissions"
                            class="px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700"
                            wire:loading.attr="disabled">
                            Enregistrer
                        </button>
                    @else
                        <span class="text-xs text-amber-600 font-medium">Rôle protégé — non modifiable</span>
                    @endif
                </div>
            </div>

            <div class="p-5 space-y-6 overflow-y-auto" style="max-height: calc(100vh - 280px)">
                @foreach ($groups as $groupName => $groupPerms)
                    @php
                        $existingPerms = array_filter($groupPerms, fn($p) => in_array($p, $allPermissions));
                    @endphp
                    @if (count($existingPerms) > 0)
                    <div>
                        <h4 class="text-xs font-semibold text-night-200 uppercase tracking-wider mb-2">
                            {{ $groupName }}
                        </h4>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach ($existingPerms as $perm)
                                <label class="flex items-center gap-2 cursor-pointer
                                    {{ $selectedRole === 'Administrateur' ? 'opacity-60 cursor-not-allowed' : '' }}">
                                    <input type="checkbox"
                                        wire:model="rolePermissions"
                                        value="{{ $perm }}"
                                        {{ $selectedRole === 'Administrateur' ? 'disabled' : '' }}
                                        class="rounded border-white/10 text-neon-400 focus:ring-indigo-500">
                                    <span class="text-xs text-night-100">{{ $perm }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="flex-1 flex items-center justify-center p-12 text-night-300">
                <div class="text-center">
                    <svg class="h-12 w-12 mx-auto mb-3 text-night-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <p class="text-sm">Sélectionner un rôle pour gérer ses permissions</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal nouveau rôle --}}
    @if ($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
        <div class="bg-night-800 rounded-xl border border-white/8 shadow-2xl w-full max-w-sm p-6" @click.stop>
            <h3 class="text-lg font-semibold mb-4">Nouveau rôle</h3>
            <form wire:submit="createRole" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-night-100">Nom du rôle *</label>
                    <input wire:model="newRoleName" type="text" autofocus
                        class="mt-1 block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500"
                        placeholder="ex: Préparateur, Responsable...">
                    @error('newRoleName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="$set('showCreateModal', false)"
                        class="px-4 py-2 text-sm text-night-200 border border-white/10 rounded-md hover:bg-night-700">
                        Annuler
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-neon-600 rounded-lg hover:bg-neon-500"
                        wire:loading.attr="disabled">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
