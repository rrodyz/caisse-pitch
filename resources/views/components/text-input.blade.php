@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-night-700 border border-white/10 text-night-50 placeholder-night-400 focus:border-neon-500 focus:ring-neon-500/30 rounded-xl shadow-sm']) }}>
