@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'name' => null,
    'rows' => 4,
])

@php
    $id = $attributes->get('id') ?? ($name ? 'ta-' . $name : 'ta-' . uniqid());
    $autocomplete = $attributes->get('autocomplete');
    if ($autocomplete === null) {
        $autocomplete = 'off';
    }

    $inputAttrs = $attributes
        ->except(['autocomplete'])
        ->merge([
            'id' => $id,
            'name' => $name,
            'autocomplete' => $autocomplete,
        ])
        ->class('textarea');

@endphp

<div class="field">
    @if($label)
        <label for="{{ $id }}" class="field__label">{{ $label }}</label>
    @endif

    <textarea rows="{{ $rows }}" {{ $inputAttrs }}></textarea>

    @if($error)
        <p class="field__error">{{ $error }}</p>
    @elseif($hint)
        <p class="field__hint">{{ $hint }}</p>
    @endif
</div>
