@props([
    'src' => null,
    'name' => null,
    'size' => 'md', // sm | md | lg
])

@php
    $classes = collect([
        'avatar',
        $size === 'sm' ? 'avatar--sm' : null,
        $size === 'lg' ? 'avatar--lg' : null,
    ])->filter()->implode(' ');

    $initials = '';
    if ($name) {
        $parts = preg_split('/\s+/', trim($name));
        foreach (array_slice($parts, 0, 2) as $p) {
            if ($p === '') continue;
            $initials .= mb_strtoupper(mb_substr($p, 0, 1));
        }
    }
    if ($initials === '') $initials = '·';
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($src)
        <img src="{{ $src }}" alt="{{ $name }}" />
    @else
        <span>{{ $initials }}</span>
    @endif
</span>
