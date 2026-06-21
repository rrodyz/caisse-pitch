<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-night-700 border border-white/10 rounded-md font-semibold text-xs text-night-200 uppercase tracking-widest hover:bg-night-600 hover:text-night-50 focus:outline-none focus:ring-2 focus:ring-neon-500/30 focus:ring-offset-2 focus:ring-offset-night-900 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
