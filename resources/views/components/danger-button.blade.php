<button {{ $attributes->merge(['type' => 'submit', 'class' => 'module-btn module-btn--danger']) }}>
    {{ $slot }}
</button>
