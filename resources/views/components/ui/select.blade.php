@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'name' => null,
])

@php
    $id = $attributes->get('id') ?? ($name ? 'sel-' . $name : 'sel-' . uniqid());
@endphp

<div class="field">
    @if($label)
        <label for="{{ $id }}" class="field__label">{{ $label }}</label>
    @endif

    <select id="{{ $id }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'select']) }}>
        {{ $slot }}
    </select>

    @if($error)
        <p class="field__error">{{ $error }}</p>
    @elseif($hint)
        <p class="field__hint">{{ $hint }}</p>
    @endif
</div>
