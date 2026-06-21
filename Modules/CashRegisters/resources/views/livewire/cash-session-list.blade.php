<div class="p-6">

    {{-- Session courante --}}
    @if ($currentSession)
        <div class="mb-5 p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-lg flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-emerald-400">
                    Session ouverte — {{ $currentSession->cashRegister?->name }}
                </p>
                <p class="text-xs text-emerald-400 mt-0.5">
                    Ouverte par {{ $currentSession->openedBy?->full_name }}
                    le {{ $currentSession->opened_at->format('d/m/Y à H:i') }}
                    · Fonds : {{ number_format($currentSession->opening_amount, 0, ',', ' ') }} FCFA
                </p>
            </div>
            @can('close-cash-session')
                <button wire:click="showCloseForm({{ $currentSession->id }})"
                    class="px-4 py-2 text-sm bg-red-600 text-white rounded-md hover:bg-red-700 font-semibold">
                    Clôturer
                </button>
            @endcan
        </div>
    @endif

    {{-- Actions --}}
    <div class="flex flex-wrap items-end gap-3 mb-4">
        <select wire:model.live="filterStatus"
            class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
            <option value="">Tous statuts</option>
            <option value="open">Ouvertes</option>
            <option value="closed">Clôturées</option>
        </select>
        <input wire:model.live="dateFrom" type="date"
            class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
        <span class="text-night-300 text-sm">→</span>
        <input wire:model.live="dateTo" type="date"
            class="border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">

        @can('open-cash-session')
            <button wire:click="showOpenForm"
                class="ml-auto px-4 py-2 bg-neon-600 text-white text-sm font-semibold rounded-lg hover:bg-neon-500">
                + Ouvrir une session
            </button>
        @endcan
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-white/5 text-sm">
            <thead class="bg-night-700">
                <tr>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Caisse</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Statut</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Ouverture</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">Fonds ouv.</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Clôture</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">Attendu</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">Compté</th>
                    <th class="px-3 py-3 text-right font-medium text-night-200">Écart</th>
                    <th class="px-3 py-3 text-left font-medium text-night-200">Opérateur</th>
                    <th class="px-3 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse ($sessions as $s)
                    <tr wire:key="cs-{{ $s->id }}">
                        <td class="px-3 py-3 font-medium text-night-50">{{ $s->cashRegister?->name }}</td>
                        <td class="px-3 py-3">
                            @php
                                $badgeCls = $s->isOpen()
                                    ? 'bg-emerald-500/15 text-emerald-300'
                                    : 'bg-night-600 text-night-300';
                            @endphp
                            <span class="inline-flex px-2 py-0.5 text-xs rounded-full font-medium {{ $badgeCls }}">
                                {{ $s->status->label() }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-xs text-night-200">{{ $s->opened_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-3 text-right text-night-100">
                            {{ number_format($s->opening_amount, 0, ',', ' ') }}
                        </td>
                        <td class="px-3 py-3 text-xs text-night-200">
                            {{ $s->closed_at?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td class="px-3 py-3 text-right text-night-200">
                            {{ $s->expected_amount !== null ? number_format($s->expected_amount, 0, ',', ' ') : '—' }}
                        </td>
                        <td class="px-3 py-3 text-right text-night-100">
                            {{ $s->closing_amount !== null ? number_format($s->closing_amount, 0, ',', ' ') : '—' }}
                        </td>
                        <td class="px-3 py-3 text-right font-semibold
                            {{ $s->gap === null ? 'text-night-300' : ($s->gap < 0 ? 'text-red-400' : ($s->gap > 0 ? 'text-emerald-400' : 'text-night-200')) }}">
                            {{ $s->gap !== null ? ($s->gap >= 0 ? '+' : '') . number_format($s->gap, 0, ',', ' ') : '—' }}
                        </td>
                        <td class="px-3 py-3 text-xs text-night-200">{{ $s->openedBy?->full_name }}</td>
                        <td class="px-3 py-3 text-right space-x-2 whitespace-nowrap">
                            @if ($s->isOpen())
                                @can('close-cash-session')
                                    <button wire:click="showCloseForm({{ $s->id }})"
                                        class="text-red-400 hover:text-red-400 text-xs font-medium">Clôturer</button>
                                @endcan
                            @else
                                <a href="{{ route('cash-sessions.report', $s->id) }}"
                                    class="text-neon-400 hover:text-neon-200 text-xs font-medium">Rapport Z</a>
                                <a href="{{ route('cash-sessions.pdf', $s->id) }}" target="_blank"
                                    class="text-night-300 hover:text-night-200 text-xs font-medium">PDF</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-4 py-8 text-center text-night-300">Aucune session.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $sessions->links() }}</div>

</div>
