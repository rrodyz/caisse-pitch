<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-30 w-60 bg-night-950 flex flex-col transition-transform duration-300 ease-in-out lg:relative lg:translate-x-0 lg:flex-shrink-0 border-r border-white/5">

    {{-- Logo --}}
    <div class="flex items-center gap-3 h-14 px-4 border-b border-white/5 flex-shrink-0">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-gold-400 to-gold-600 flex items-center justify-center flex-shrink-0 shadow-lg">
            <svg class="h-5 w-5 text-night-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
        </div>
        <a href="{{ route('dashboard') }}" class="text-night-50 font-bold text-sm tracking-wide truncate">
            {{ config('app.name') }}
        </a>
        <button @click="sidebarOpen = false"
                class="ml-auto lg:hidden p-1 rounded text-night-300 hover:text-white hover:bg-night-700 transition-colors">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto py-3 px-2.5 space-y-0.5">

        @php
            $lnk = function(string $pattern): string {
                $a = request()->routeIs($pattern);
                return 'group flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150 '
                    . ($a
                        ? 'bg-gold-400/10 text-gold-300 border border-gold-400/20'
                        : 'text-night-200 hover:bg-white/5 hover:text-night-50 border border-transparent');
            };
            $section = 'mt-5 mb-1.5 px-3 text-[10px] font-bold text-night-300 uppercase tracking-[0.12em]';
        @endphp

        {{-- ── Tableau de bord ── --}}
        @can('view-dashboard')
        <a href="{{ route('dashboard') }}" class="{{ $lnk('dashboard') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Tableau de bord
        </a>
        @endcan

        {{-- ── POS Caisse ── --}}
        @can('create-sales')
        <a href="{{ route('pos.index') }}"
           class="group flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-bold transition-all duration-150 mt-2
                {{ request()->routeIs('pos.*')
                    ? 'bg-gold-400/15 text-gold-300 border border-gold-400/25'
                    : 'bg-neon-600/10 text-neon-300 hover:bg-neon-600/20 border border-neon-500/20 hover:border-neon-500/40' }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            Caisse — POS
        </a>
        @endcan

        {{-- ── Ventes ── --}}
        @canany(['view-sales'])
        <p class="{{ $section }}">Ventes</p>

        <a href="{{ route('sales.index') }}" class="{{ $lnk('sales.*') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Historique ventes
        </a>

        <a href="{{ route('customers.index') }}" class="{{ $lnk('customers.*') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Clients & crédits
        </a>
        @endcanany

        {{-- ── Catalogue ── --}}
        @canany(['view-products', 'view-categories', 'view-recipes'])
        <p class="{{ $section }}">Catalogue</p>
        @endcanany

        @can('view-products')
        <a href="{{ route('products.index') }}" class="{{ $lnk('products.*') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Produits
        </a>
        @endcan

        @can('view-categories')
        <a href="{{ route('categories.index') }}" class="{{ $lnk('categories.*') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            Catégories
        </a>
        @endcan

        @can('view-recipes')
        <a href="{{ route('recipes.index') }}" class="{{ $lnk('recipes.*') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
            Recettes & cocktails
        </a>
        @endcan

        {{-- ── Stock ── --}}
        @canany(['view-stock', 'view-losses', 'manage-inventory'])
        <p class="{{ $section }}">Stock</p>
        @endcanany

        @can('view-stock')
        <a href="{{ route('stock.index') }}" class="{{ $lnk('stock.*') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
            Mouvements stock
        </a>
        @endcan

        @can('view-losses')
        <a href="{{ route('losses.index') }}" class="{{ $lnk('losses.*') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Pertes & casses
        </a>
        @endcan

        @can('manage-inventory')
        <a href="{{ route('inventories.index') }}" class="{{ $lnk('inventories.*') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            Inventaires
        </a>
        @endcan

        {{-- ── Achats ── --}}
        @canany(['view-purchases', 'view-suppliers'])
        <p class="{{ $section }}">Achats</p>
        @endcanany

        @can('view-purchases')
        <a href="{{ route('purchases.index') }}" class="{{ $lnk('purchases.*') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            Commandes
        </a>
        @endcan

        @can('view-suppliers')
        <a href="{{ route('suppliers.index') }}" class="{{ $lnk('suppliers.*') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
            </svg>
            Fournisseurs
        </a>
        @endcan

        {{-- ── Caisses ── --}}
        @canany(['view-cash-registers', 'view-cash-sessions'])
        <p class="{{ $section }}">Caisses</p>
        @endcanany

        @can('view-cash-registers')
        <a href="{{ route('cash-registers.index') }}" class="{{ $lnk('cash-registers.*') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Caisses
        </a>
        @endcan

        @can('view-cash-sessions')
        <a href="{{ route('cash-sessions.index') }}" class="{{ $lnk('cash-sessions.*') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Sessions de caisse
        </a>
        @endcan

        {{-- ── Rapports ── --}}
        @can('view-reports')
        <p class="{{ $section }}">Rapports</p>

        <a href="{{ route('margins.index') }}" class="{{ $lnk('margins.*') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
            Marges
        </a>

        <a href="{{ route('reports.sales') }}" class="{{ $lnk('reports.sales') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Rapport ventes
        </a>

        <a href="{{ route('reports.stock') }}" class="{{ $lnk('reports.stock') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
            </svg>
            Rapport stock
        </a>

        <a href="{{ route('reports.losses') }}" class="{{ $lnk('reports.losses') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Rapport pertes
        </a>

        <a href="{{ route('reports.payments') }}" class="{{ $lnk('reports.payments') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            Rapport paiements
        </a>
        @endcan

        {{-- ── Administration ── --}}
        @canany(['view-users', 'manage-roles', 'view-settings', 'view-activity-logs'])
        <p class="{{ $section }}">Administration</p>
        @endcanany

        @can('view-users')
        <a href="{{ route('users.index') }}" class="{{ $lnk('users.*') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            Utilisateurs
        </a>
        @endcan

        @can('manage-roles')
        <a href="{{ route('roles.index') }}" class="{{ $lnk('roles.*') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Rôles & Permissions
        </a>
        @endcan

        @can('view-activity-logs')
        <a href="{{ route('activity-log.index') }}" class="{{ $lnk('activity-log.*') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            Journal d'activité
        </a>
        @endcan

        @can('view-settings')
        <a href="{{ route('settings.index') }}" class="{{ $lnk('settings.*') }}">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Paramètres
        </a>
        @endcan

    </nav>

    {{-- User footer --}}
    <div class="border-t border-white/5 p-3 flex-shrink-0">
        <div class="flex items-center gap-2.5 mb-2">
            <div class="h-8 w-8 rounded-full bg-gradient-to-br from-neon-500 to-neon-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                {{ strtoupper(substr(Auth::user()->first_name ?? '', 0, 1) . substr(Auth::user()->last_name ?? '', 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-night-50 truncate">{{ Auth::user()->full_name }}</p>
                <p class="text-xs text-night-300 truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-night-300 hover:text-white hover:bg-night-700 rounded-lg transition-colors duration-150">
                <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Déconnexion
            </button>
        </form>
    </div>
</aside>
