@props([
    'label' => null,
    'round' => false,
    'size' => 'md',     // md | lg
    'name' => null,
])

@php
    $classes = collect([
        'check',
        $round ? 'check--round' : null,
        $size === 'lg' ? 'check--lg' : null,
    ])->filter()->implode(' ');
@endphp

<label class="{{ $classes }}">
    <input type="checkbox" name="{{ $name }}" {{ $attributes->except(['class']) }} />
    <span class="check__box">
        <x-ui.icon name="check-2" :size="12" />
    </span>
    @if($label !== null)
        <span class="check__label">{{ $label }}</span>
    @elseif(trim($slot) !== '')
        <span class="check__label">{{ $slot }}</span>
    @endif
</label>
