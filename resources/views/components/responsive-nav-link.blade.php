@props(['active' => false])

<a {{ $attributes->except('class')->merge() }} @class([
    'block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-base font-medium text-base-content bg-base-200 transition duration-150 ease-in-out hover:bg-accent hover:bg-opacity-20',
    'border-accent-focus text-base-content font-bold bg-accent bg-opacity-20' => $active,
])>
    {{ $slot }}
</a>
