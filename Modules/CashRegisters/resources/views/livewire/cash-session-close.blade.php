<div class="p-6 max-w-lg mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <button wire:click="cancelForm" class="text-night-300 hover:text-night-200 text-sm">← Sessions</button>
        <h3 class="text-lg font-semibold text-night-50">Clôturer la session</h3>
    </div>

    {{-- Récapitulatif --}}
    <div class="bg-night-700 border border-white/8 rounded-lg p-4 mb-6 space-y-2 text-sm">
        <div class="flex justify-between">
            <span class="text-night-200">Caisse</span>
            <span class="font-medium text-night-50">{{ $session->cashRegister?->name }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-night-200">Ouverte par</span>
            <span class="text-night-100">{{ $session->openedBy?->full_name }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-night-200">Ouverture</span>
            <span class="text-night-100">{{ $session->opened_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-night-200">Durée</span>
            <span class="text-night-100">{{ $session->duration() }}</span>
        </div>
        <div class="flex justify-between border-t border-white/8 pt-2">
            <span class="text-night-200">Fonds d'ouverture</span>
            <span class="font-semibold text-night-50">{{ number_format($session->opening_amount, 0, ',', ' ') }} FCFA</span>
        </div>
    </div>

    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-night-200 mb-1">Montant compté en caisse (FCFA) *</label>
            <input wire:model="closingAmount" type="number" step="1" min="0"
                class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500"
                placeholder="0">
            @error('closingAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-night-200 mb-1">Notes de clôture</label>
            <textarea wire:model="closingNotes" rows="2"
                class="block w-full border-white/10 rounded-md shadow-sm text-sm focus:ring-neon-500/30 focus:border-neon-500"></textarea>
        </div>
    </div>

    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-white/8">
        <button wire:click="cancelForm"
            class="px-4 py-2 text-sm text-night-200 border border-white/10 rounded-md hover:bg-night-700">
            Annuler
        </button>
        <button wire:click="closeSession" wire:loading.attr="disabled"
            wire:confirm="Confirmer la clôture de la session ?"
            class="px-4 py-2 text-sm text-white bg-red-600 rounded-md hover:bg-red-700 disabled:opacity-50">
            <span wire:loading.remove>Clôturer la session</span>
            <span wire:loading>Clôture...</span>
        </button>
    </div>

</div>
