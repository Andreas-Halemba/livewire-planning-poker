<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-error transition ease-in-out duration-150 cursor-pointer']) }}>
    {{ $slot }}
</button>
