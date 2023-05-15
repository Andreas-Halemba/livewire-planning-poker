<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-success transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
