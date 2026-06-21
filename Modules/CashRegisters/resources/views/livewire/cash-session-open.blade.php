<div class="p-6 max-w-lg mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <button wire:click="cancelForm" class="text-night-300 hover:text-night-200 text-sm">← Sessions</button>
        <h3 class="text-lg font-semibold text-night-50">Ouvrir une session de caisse</h3>
    </div>

    <div class="space-y-5">
        <div>
            <label class="block text-sm font-medium text-night-200 mb-1">Caisse *</label>
            <select wire:model="openRegisterId"
                class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500">
                <option value="">— Sélectionner une caisse —</option>
                @foreach ($availableRegisters as $reg)
                    <option value="{{ $reg->id }}" {{ $reg->hasOpenSession() ? 'disabled' : '' }}>
                        {{ $reg->name }}
                        {{ $reg->location ? "— {$reg->location}" : '' }}
                        {{ $reg->hasOpenSession() ? '(session en cours)' : '' }}
                    </option>
                @endforeach
            </select>
            @error('openRegisterId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-night-200 mb-1">Fonds de caisse (FCFA) *</label>
            <input wire:model="openingAmount" type="number" step="1" min="0"
                class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500"
                placeholder="0">
            @error('openingAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            <p class="text-xs text-night-300 mt-1">Montant en espèces présent dans la caisse à l'ouverture.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-night-200 mb-1">Notes d'ouverture</label>
            <textarea wire:model="openingNotes" rows="2"
                class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500"></textarea>
        </div>
    </div>

    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-white/8">
        <button wire:click="cancelForm"
            class="px-4 py-2 text-sm text-night-200 border border-white/10 rounded-md hover:bg-night-700">
            Annuler
        </button>
        <button wire:click="openSession" wire:loading.attr="disabled"
            class="px-4 py-2 text-sm text-white bg-green-600 rounded-md hover:bg-green-700 disabled:opacity-50">
            <span wire:loading.remove>Ouvrir la session</span>
            <span wire:loading>Ouverture...</span>
        </button>
    </div>

</div>
