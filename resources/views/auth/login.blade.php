<x-guest-layout>

    @if (session('status'))
        <div style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.25);border-radius:.5rem;padding:.625rem .875rem;color:#34d399;font-size:.8125rem;margin-bottom:1rem">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" style="display:flex;flex-direction:column;gap:1.125rem">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="field-label">Adresse email</label>
            <div class="field-wrap">
                <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 7 10-7"/></svg>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                    class="field-input" required autofocus autocomplete="username"
                    placeholder="votre@email.com">
            </div>
            @error('email')
                <p style="margin:.375rem 0 0;font-size:.75rem;color:#f87171">{{ $message }}</p>
            @enderror
        </div>

        {{-- Mot de passe --}}
        <div>
            <label for="password" class="field-label">Mot de passe</label>
            <div class="field-wrap">
                <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                <input id="password" type="password" name="password"
                    class="field-input" style="padding-right:2.5rem"
                    required autocomplete="current-password" placeholder="••••••••">
                <button type="button" class="pw-btn" onclick="togglePw('password',this)" aria-label="Afficher/masquer">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            @error('password')
                <p style="margin:.375rem 0 0;font-size:.75rem;color:#f87171">{{ $message }}</p>
            @enderror
        </div>

        {{-- Se souvenir + mot de passe oublié --}}
        <div style="display:flex;align-items:center;justify-content:space-between">
            <label class="check-wrap">
                <input type="checkbox" name="remember" class="custom-cb">
                <span style="font-size:.8125rem;color:#545470">Se souvenir de moi</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                    style="font-size:.8125rem;color:#8b5cf6;text-decoration:none;transition:color .15s"
                    onmouseover="this.style.color='#a78bfa'" onmouseout="this.style.color='#8b5cf6'">
                    Mot de passe oublié ?
                </a>
            @endif
        </div>

        {{-- Bouton --}}
        <button type="submit" class="btn-login" style="margin-top:.25rem">
            Se connecter
        </button>
    </form>

</x-guest-layout>
