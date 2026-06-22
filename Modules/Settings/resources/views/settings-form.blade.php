<form wire:submit="save" class="space-y-6">

    {{-- ── Établissement ─────────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-white/6 overflow-hidden" style="background:#0d0d18">
        <div class="flex items-center gap-3 px-5 py-3.5 border-b border-white/6" style="background:#161625">
            <span class="flex items-center justify-center w-7 h-7 rounded-lg" style="background:rgba(139,92,246,.15)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </span>
            <h3 class="panel-header" style="color:#88889a;margin:0">Établissement</h3>
        </div>
        <div class="p-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-night-300 uppercase tracking-wider mb-1.5">Nom de l'établissement *</label>
                <input wire:model="establishment_name" type="text"
                    class="block w-full bg-night-800 border border-white/8 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500 px-3 py-2.5">
                @error('establishment_name') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-night-300 uppercase tracking-wider mb-1.5">Adresse</label>
                <textarea wire:model="address" rows="2"
                    class="block w-full bg-night-800 border border-white/8 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500 px-3 py-2.5"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-night-300 uppercase tracking-wider mb-1.5">Téléphone</label>
                <input wire:model="phone" type="text"
                    class="block w-full bg-night-800 border border-white/8 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500 px-3 py-2.5">
            </div>
            <div>
                <label class="block text-xs font-semibold text-night-300 uppercase tracking-wider mb-1.5">Email</label>
                <input wire:model="email" type="email"
                    class="block w-full bg-night-800 border border-white/8 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500 px-3 py-2.5">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-night-300 uppercase tracking-wider mb-1.5">Logo</label>
                <input wire:model="logo" type="file" accept="image/*"
                    class="block w-full text-sm text-night-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-night-700 file:text-night-200">
                @error('logo') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    {{-- ── Devise & Taxes ─────────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-white/6 overflow-hidden" style="background:#0d0d18">
        <div class="flex items-center gap-3 px-5 py-3.5 border-b border-white/6" style="background:#161625">
            <span class="flex items-center justify-center w-7 h-7 rounded-lg" style="background:rgba(212,175,55,.12)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/><path d="M16 6l-4 2-4-2M8 18l4-2 4 2"/></svg>
            </span>
            <h3 class="panel-header" style="color:#88889a;margin:0">Devise &amp; Taxes</h3>
        </div>
        <div class="p-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-xs font-semibold text-night-300 uppercase tracking-wider mb-1.5">Devise (libellé)</label>
                <input wire:model="currency" type="text" placeholder="FCFA"
                    class="block w-full bg-night-800 border border-white/8 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500 px-3 py-2.5">
            </div>
            <div>
                <label class="block text-xs font-semibold text-night-300 uppercase tracking-wider mb-1.5">Code ISO</label>
                <input wire:model="currency_code" type="text" maxlength="3" placeholder="XOF"
                    class="block w-full bg-night-800 border border-white/8 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500 px-3 py-2.5 font-mono uppercase">
            </div>
            <div>
                <label class="block text-xs font-semibold text-night-300 uppercase tracking-wider mb-1.5">Taux TVA (%)</label>
                <input wire:model="tax_rate" type="number" step="0.01" min="0" max="100"
                    class="block w-full bg-night-800 border border-white/8 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500 px-3 py-2.5">
                @error('tax_rate') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    {{-- ── Tickets de caisse ──────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-white/6 overflow-hidden" style="background:#0d0d18">
        <div class="flex items-center gap-3 px-5 py-3.5 border-b border-white/6" style="background:#161625">
            <span class="flex items-center justify-center w-7 h-7 rounded-lg" style="background:rgba(52,211,153,.1)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </span>
            <h3 class="panel-header" style="color:#88889a;margin:0">Tickets de caisse</h3>
        </div>
        <div class="p-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-xs font-semibold text-night-300 uppercase tracking-wider mb-1.5">Préfixe numérotation</label>
                <input wire:model="ticket_number_prefix" type="text" placeholder="TKT"
                    class="block w-full bg-night-800 border border-white/8 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500 px-3 py-2.5 font-mono uppercase">
            </div>
            <div>
                <label class="block text-xs font-semibold text-night-300 uppercase tracking-wider mb-1.5">Nombre de chiffres</label>
                <input wire:model="ticket_number_padding" type="number" min="4" max="10"
                    class="block w-full bg-night-800 border border-white/8 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500 px-3 py-2.5">
            </div>
            <div class="sm:col-span-3">
                <label class="block text-xs font-semibold text-night-300 uppercase tracking-wider mb-1.5">Message de remerciement</label>
                <textarea wire:model="ticket_message" rows="2" placeholder="Merci de votre visite !"
                    class="block w-full bg-night-800 border border-white/8 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500 px-3 py-2.5"></textarea>
            </div>
        </div>
    </div>

    {{-- ── Stock & Remises ────────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-white/6 overflow-hidden" style="background:#0d0d18">
        <div class="flex items-center gap-3 px-5 py-3.5 border-b border-white/6" style="background:#161625">
            <span class="flex items-center justify-center w-7 h-7 rounded-lg" style="background:rgba(239,68,68,.1)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            </span>
            <h3 class="panel-header" style="color:#88889a;margin:0">Stock &amp; Remises</h3>
        </div>
        <div class="p-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-xs font-semibold text-night-300 uppercase tracking-wider mb-1.5">Seuil alerte stock (global)</label>
                <input wire:model="stock_alert_threshold" type="number" min="0"
                    class="block w-full bg-night-800 border border-white/8 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500 px-3 py-2.5">
                @error('stock_alert_threshold') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-night-300 uppercase tracking-wider mb-1.5">Remise max (%)</label>
                <input wire:model="max_discount_percent" type="number" step="0.01" min="0" max="100"
                    class="block w-full bg-night-800 border border-white/8 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500 px-3 py-2.5">
                @error('max_discount_percent') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-night-300 uppercase tracking-wider mb-1.5">Seuil supervision (annulation)</label>
                <input wire:model="supervisor_approval_threshold" type="number" step="0.01" min="0"
                    placeholder="Vide = toujours"
                    class="block w-full bg-night-800 border border-white/8 rounded-lg text-night-50 text-sm focus:ring-neon-500/30 focus:border-neon-500 px-3 py-2.5">
            </div>
        </div>
    </div>

    {{-- ── Footer save ────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-end gap-4 pt-2">
        @can('edit-settings')
            <span wire:loading wire:target="save" class="text-xs text-night-300">Enregistrement…</span>
            <button type="submit" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-bold text-white disabled:opacity-50 transition-all"
                style="background:linear-gradient(135deg,#5b21b6,#7c3aed);box-shadow:0 4px 16px rgba(109,40,217,.35)">
                <svg wire:loading.remove wire:target="save" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span wire:loading.remove wire:target="save">Enregistrer les paramètres</span>
                <span wire:loading wire:target="save">…</span>
            </button>
        @endcan
    </div>

</form>
