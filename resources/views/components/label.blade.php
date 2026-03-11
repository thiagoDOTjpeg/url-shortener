@props(['for'])
<label {{ $attributes->merge(['for' => $for, 'class' => 'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70']) }}>
    {{ $slot }}
</label>
