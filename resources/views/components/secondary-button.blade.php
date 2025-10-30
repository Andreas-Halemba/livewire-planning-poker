<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-secondary transition ease-in-out duration-150 cursor-pointer']) }}>
    {{ $slot }}
</button>
