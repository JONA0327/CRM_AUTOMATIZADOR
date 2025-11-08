<button {{ $attributes->merge(['type' => 'button', 'class' => 'module-btn module-btn--secondary']) }}>
    {{ $slot }}
</button>
