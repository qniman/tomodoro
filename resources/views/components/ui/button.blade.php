@props([
    'variant' => 'default',
    'size' => 'md',
    'icon' => null,
    'iconRight' => null,
    'iconOnly' => false,
    'type' => 'button',
    'href' => null,
    'wireTarget' => null,
])

@php
    $classes = collect([
        'btn',
        $variant !== 'default' ? 'btn--' . $variant : null,
        $size !== 'md' ? 'btn--' . $size : null,
        $iconOnly ? 'btn--icon' : null,
    ])->filter()->implode(' ');

    $attrs = $attributes->merge(['class' => $classes]);
    $loadingAttrs = $wireTarget
        ? "wire:loading.attr=\"data-loading\" wire:target=\"{$wireTarget}\""
        : '';
@endphp

@if($href)
    <a href="{{ $href }}" {!! $attrs !!}>
        @if($icon)
            <x-ui.icon :name="$icon" :size="$size === 'sm' ? 14 : 16" />
        @endif
        @if(! $iconOnly)
            <span>{{ $slot }}</span>
        @endif
        @if($iconRight)
            <x-ui.icon :name="$iconRight" :size="$size === 'sm' ? 14 : 16" />
        @endif
    </a>
@else
    <button type="{{ $type }}" {!! $attrs !!} {!! $loadingAttrs !!}>
        @if($icon)
            <x-ui.icon :name="$icon" :size="$size === 'sm' ? 14 : 16" />
        @endif
        @if(! $iconOnly)
            <span>{{ $slot }}</span>
        @endif
        @if($iconRight)
            <x-ui.icon :name="$iconRight" :size="$size === 'sm' ? 14 : 16" />
        @endif
    </button>
@endif
