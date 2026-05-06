@props([
    'variant' => 'default', // default | accent | success | warning | danger
    'dot' => false,
])

@php
    $classes = collect([
        'badge',
        $variant !== 'default' ? 'badge--' . $variant : null,
        $dot ? 'badge--dot' : null,
    ])->filter()->implode(' ');
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)<span class="badge__dot"></span>@endif
    {{ $slot }}
</span>
