@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'name' => null,
    'rows' => 4,
])

@php
    $id = $attributes->get('id') ?? ($name ? 'ta-' . $name : 'ta-' . uniqid());
@endphp

<div class="field">
    @if($label)
        <label for="{{ $id }}" class="field__label">{{ $label }}</label>
    @endif

    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->merge(['class' => 'textarea']) }}></textarea>

    @if($error)
        <p class="field__error">{{ $error }}</p>
    @elseif($hint)
        <p class="field__hint">{{ $hint }}</p>
    @endif
</div>
