@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'name' => null,
])

@php
    $id = $attributes->get('id') ?? ($name ? 'sel-' . $name : 'sel-' . uniqid());
    $autocomplete = $attributes->get('autocomplete');
    if ($autocomplete === null) {
        $autocomplete = 'off';
    }

    $selectAttrs = $attributes
        ->except(['autocomplete'])
        ->merge([
            'id' => $id,
            'name' => $name,
            'autocomplete' => $autocomplete,
        ])
        ->class('select');
@endphp

<div class="field">
    @if($label)
        <label for="{{ $id }}" class="field__label">{{ $label }}</label>
    @endif

    <select {{ $selectAttrs }}>
        {{ $slot }}
    </select>

    @if($error)
        <p class="field__error">{{ $error }}</p>
    @elseif($hint)
        <p class="field__hint">{{ $hint }}</p>
    @endif
</div>
