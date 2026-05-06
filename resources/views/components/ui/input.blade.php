@props([
    'type' => 'text',
    'label' => null,
    'hint' => null,
    'error' => null,
    'name' => null,
    'icon' => null,         // подкладывает иконку в input-group слева
    'size' => 'md',
])

@php
    $id = $attributes->get('id') ?? ($name ? 'input-' . $name : 'input-' . uniqid());
    $inputAttrs = $attributes->merge([
        'class' => 'input' . ($size === 'lg' ? ' input--lg' : ''),
        'id' => $id,
        'name' => $name,
    ])->except(['class']);
@endphp

<div class="field">
    @if($label)
        <label for="{{ $id }}" class="field__label">{{ $label }}</label>
    @endif

    @if($icon)
        <div class="input-group">
            <span class="input-group__addon"><x-ui.icon :name="$icon" :size="16" /></span>
            <input type="{{ $type }}" {{ $inputAttrs }} />
        </div>
    @else
        <input type="{{ $type }}" {{ $inputAttrs }} />
    @endif

    @if($error)
        <p class="field__error">{{ $error }}</p>
    @elseif($hint)
        <p class="field__hint">{{ $hint }}</p>
    @endif
</div>
