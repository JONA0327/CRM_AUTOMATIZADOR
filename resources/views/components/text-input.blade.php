@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-gray-800 border border-white/10 text-gray-100 placeholder-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) }}>
