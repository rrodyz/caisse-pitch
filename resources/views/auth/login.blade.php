<x-guest-layout>
    {{-- Statut session (ex: mot de passe réinitialisé) --}}
    @if (session('status'))
        <div class="mb-4 text-sm text-emerald-600 font-medium">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium mb-1" style="color:#4b5563">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                required autofocus autocomplete="username"
                class="w-full px-4 py-2.5 border rounded-xl text-sm transition-colors
                       focus:outline-none focus:ring-2 focus:ring-neon-400/40 focus:border-neon-400"
                style="background-color:#f9fafb;border-color:#e5e7eb;color:#1f2937;--placeholder-color:#d1d5db"
                placeholder="votre@email.com">
            @error('email')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Mot de passe --}}
        <div>
            <label for="password" class="block text-sm font-medium mb-1" style="color:#4b5563">Mot de passe</label>
            <input id="password" type="password" name="password"
                required autocomplete="current-password"
                class="w-full px-4 py-2.5 border rounded-xl text-sm transition-colors
                       focus:outline-none focus:ring-2 focus:ring-neon-400/40 focus:border-neon-400"
                style="background-color:#f9fafb;border-color:#e5e7eb;color:#1f2937"
                placeholder="••••••••">
            @error('password')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Se souvenir + mot de passe oublié --}}
        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-gray-500 cursor-pointer select-none">
                <input type="checkbox" name="remember"
                    class="rounded border-gray-300 text-neon-500 shadow-sm focus:ring-neon-400/30">
                <span>Se souvenir de moi</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                    class="text-neon-500 hover:text-neon-600 transition-colors">
                    Mot de passe oublié ?
                </a>
            @endif
        </div>

        {{-- Bouton connexion --}}
        <button type="submit"
            class="w-full py-3 font-semibold rounded-xl text-sm uppercase tracking-widest
                   focus:outline-none focus:ring-2 focus:ring-neon-400/50
                   transition-all duration-150 mt-2"
            style="background:linear-gradient(to right,#5b21b6,#7c3aed);color:#ffffff;box-shadow:0 4px 14px rgba(91,33,182,0.4)">
            Se connecter
        </button>
    </form>
</x-guest-layout>
