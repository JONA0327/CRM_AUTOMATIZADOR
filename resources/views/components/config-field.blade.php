@props([
    'name',
    'label',
    'tipo'        => 'text',
    'placeholder' => '',
    'configured'  => false,
])

<div x-data="{ show: false }">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1.5">
        {{ $label }}
        @if ($configured)
            <span class="ml-1 text-xs text-green-600 font-normal">✓ guardado</span>
        @endif
    </label>

    <div class="relative">
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            :type="show ? 'text' : '{{ $tipo }}'"
            placeholder="{{ $configured ? '••••• — dejar vacío para mantener' : $placeholder }}"
            autocomplete="off"
            class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm
                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition
                   {{ $configured ? 'border-green-300 bg-green-50/40' : '' }}"
        >

        {{-- Botón mostrar/ocultar (solo para campos password) --}}
        @if ($tipo === 'password')
            <button type="button"
                    @click="show = !show"
                    class="absolute inset-y-0 right-0 px-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
            </button>
        @endif
    </div>
</div>
