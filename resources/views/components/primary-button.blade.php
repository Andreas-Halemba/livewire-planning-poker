<button
    {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-primary btn-outline btn transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
