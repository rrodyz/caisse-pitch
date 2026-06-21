<div class="p-6">

    {{-- Filtres --}}
    <div class="flex flex-wrap items-end gap-3 mb-6">
        <div>
            <label class="block text-xs text-night-300 mb-1">Recherche</label>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Description ou modèle..."
                class="border-white/10 rounded-md shadow-sm text-sm w-48 focus:ring-neon-500/30 focus:border-neon-500">
        </div>
        <div>
            <label class="block text-xs text-night-300 mb-1">Action</label>
            <select wire:model.live="filterAction"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                <option value="">Toutes les actions</option>
                <option value="created">Création</option>
                <option value="updated">Modification</option>
                <option value="deleted">Suppression</option>
                <option value="custom">Autre</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-night-300 mb-1">Utilisateur</label>
            <select wire:model.live="filterUser"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                <option value="">Tous les utilisateurs</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-night-300 mb-1">Modèle</label>
            <select wire:model.live="filterModel"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                <option value="">Tous les modèles</option>
                @foreach($models as $m)
                    <option value="{{ $m['value'] }}">{{ $m['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-night-300 mb-1">Du</label>
            <input wire:model.live="dateFrom" type="date"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
        </div>
        <div>
            <label class="block text-xs text-night-300 mb-1">Au</label>
            <input wire:model.live="dateTo" type="date"
                class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/5 text-sm">
            <thead class="bg-night-700">
                <tr>
                    <th class="px-3 py-3 text-left font-medium text-night-200 whitespace-nowrap">Date</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Utilisateur</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Action</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Modèle</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Description</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Changements</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($query as $log)
                    @php
                        $actionBadge = match($log->action) {
                            'created' => 'bg-emerald-500/15 text-emerald-300',
                            'updated' => 'bg-blue-500/15 text-blue-300',
                            'deleted' => 'bg-red-500/15 text-red-300',
                            default   => 'bg-amber-500/15 text-amber-300',
                        };
                        $actionLabel = match($log->action) {
                            'created' => 'Création',
                            'updated' => 'Modification',
                            'deleted' => 'Suppression',
                            default   => $log->action,
                        };
                        $modelName = $log->subject_type ? class_basename($log->subject_type) : '—';
                    @endphp
                    <tr wire:key="log-{{ $log->id }}">
                        <td class="px-3 py-3 text-night-300 text-xs whitespace-nowrap">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-3 py-3 text-night-200 text-xs">
                            {{ $log->user?->first_name . ' ' . $log->user?->last_name ?? 'Système' }}
                        </td>
                        <td class="px-3 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $actionBadge }}">
                                {{ $actionLabel }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-night-200 text-xs">
                            <span class="font-medium">{{ $modelName }}</span>
                            @if($log->subject_id)
                                <span class="text-night-400"> #{{ $log->subject_id }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-night-300 text-xs max-w-xs">
                            {{ $log->description ?: '—' }}
                        </td>
                        <td class="px-3 py-3 text-xs">
                            @if($log->old_values || $log->new_values)
                                <details class="cursor-pointer">
                                    <summary class="text-neon-400 hover:text-neon-300 select-none">
                                        Voir les détails
                                    </summary>
                                    <div class="mt-2 space-y-1">
                                        @if($log->old_values)
                                            <div class="text-night-400 font-medium">Avant :</div>
                                            @foreach($log->old_values as $key => $val)
                                                <div class="ml-2">
                                                    <span class="text-night-400">{{ $key }}:</span>
                                                    <span class="text-red-300">{{ is_array($val) ? json_encode($val) : $val }}</span>
                                                </div>
                                            @endforeach
                                        @endif
                                        @if($log->new_values)
                                            <div class="text-night-400 font-medium mt-1">Après :</div>
                                            @foreach($log->new_values as $key => $val)
                                                <div class="ml-2">
                                                    <span class="text-night-400">{{ $key }}:</span>
                                                    <span class="text-emerald-300">{{ is_array($val) ? json_encode($val) : $val }}</span>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </details>
                            @else
                                <span class="text-night-500">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-night-300">
                            Aucun événement trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $query->links() }}</div>

</div>
