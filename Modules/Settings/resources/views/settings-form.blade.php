<form wire:submit="save" class="space-y-8">

    {{-- Informations établissement --}}
    <div>
        <h3 class="text-lg font-semibold text-night-50 mb-4">Établissement</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-night-200">Nom de l'établissement *</label>
                <input wire:model="establishment_name" type="text"
                    class="mt-1 block w-full bg-night-700 border border-white/10 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500">
                @error('establishment_name') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-night-200">Adresse</label>
                <textarea wire:model="address" rows="2"
                    class="mt-1 block w-full bg-night-700 border border-white/10 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-night-200">Téléphone</label>
                <input wire:model="phone" type="text"
                    class="mt-1 block w-full bg-night-700 border border-white/10 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-night-200">Email</label>
                <input wire:model="email" type="email"
                    class="mt-1 block w-full bg-night-700 border border-white/10 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-night-200">Logo</label>
                <input wire:model="logo" type="file" accept="image/*"
                    class="mt-1 block w-full text-sm text-night-200">
                @error('logo') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <hr class="border-white/8">

    {{-- Devise & Taxes --}}
    <div>
        <h3 class="text-lg font-semibold text-night-50 mb-4">Devise & Taxes</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-night-200">Devise (libellé)</label>
                <input wire:model="currency" type="text" placeholder="FCFA"
                    class="mt-1 block w-full bg-night-700 border border-white/10 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-night-200">Code ISO</label>
                <input wire:model="currency_code" type="text" maxlength="3" placeholder="XOF"
                    class="mt-1 block w-full bg-night-700 border border-white/10 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-night-200">Taux TVA (%)</label>
                <input wire:model="tax_rate" type="number" step="0.01" min="0" max="100"
                    class="mt-1 block w-full bg-night-700 border border-white/10 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500">
                @error('tax_rate') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <hr class="border-white/8">

    {{-- Tickets --}}
    <div>
        <h3 class="text-lg font-semibold text-night-50 mb-4">Tickets de caisse</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-night-200">Préfixe numérotation</label>
                <input wire:model="ticket_number_prefix" type="text" placeholder="TKT"
                    class="mt-1 block w-full bg-night-700 border border-white/10 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-night-200">Nombre de chiffres</label>
                <input wire:model="ticket_number_padding" type="number" min="4" max="10"
                    class="mt-1 block w-full bg-night-700 border border-white/10 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500">
            </div>
            <div class="sm:col-span-3">
                <label class="block text-sm font-medium text-night-200">Message de remerciement</label>
                <textarea wire:model="ticket_message" rows="2" placeholder="Merci de votre visite !"
                    class="mt-1 block w-full bg-night-700 border border-white/10 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500"></textarea>
            </div>
        </div>
    </div>

    <hr class="border-white/8">

    {{-- Stock & Remises --}}
    <div>
        <h3 class="text-lg font-semibold text-night-50 mb-4">Stock & Remises</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-night-200">Seuil alerte stock (global)</label>
                <input wire:model="stock_alert_threshold" type="number" min="0"
                    class="mt-1 block w-full bg-night-700 border border-white/10 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500">
                @error('stock_alert_threshold') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-night-200">Remise max (%)</label>
                <input wire:model="max_discount_percent" type="number" step="0.01" min="0" max="100"
                    class="mt-1 block w-full bg-night-700 border border-white/10 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500">
                @error('max_discount_percent') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-night-200">Seuil validation superviseur (annulation)</label>
                <input wire:model="supervisor_approval_threshold" type="number" step="0.01" min="0"
                    placeholder="Laisser vide = toujours"
                    class="mt-1 block w-full bg-night-700 border border-white/10 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500">
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-4 pt-4 border-t border-white/8">
        @if (session('success'))
            <span class="text-sm text-emerald-400">{{ session('success') }}</span>
        @endif
        @can('edit-settings')
            <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-t border-white/8ransparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                wire:loading.attr="disabled">
                <span wire:loading.remove>Enregistrer</span>
                <span wire:loading>Enregistrement...</span>
            </button>
        @endcan
    </div>

</form>
