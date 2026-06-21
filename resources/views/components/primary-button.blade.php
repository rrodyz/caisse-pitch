<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-neon-600 border border-transparent rounded-xl font-semibold text-sm text-white uppercase tracking-widest hover:bg-neon-500 focus:bg-neon-500 active:bg-neon-700 focus:outline-none focus:ring-2 focus:ring-neon-500/40 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
