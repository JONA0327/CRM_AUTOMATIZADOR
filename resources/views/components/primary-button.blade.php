<button {{ $attributes->merge(['type' => 'submit', 'class' => 'module-btn module-btn--primary']) }}>
    {{ $slot }}
</button>
