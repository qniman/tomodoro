@props([
    'type' => 'text',
    'label' => null,
    'hint' => null,
    'error' => null,
    'name' => null,
    'icon' => null,
    'size' => 'md',
])

@php
    $id = $attributes->get('id') ?? ($name ? 'input-' . $name : 'input-' . uniqid());
    $autocomplete = $attributes->get('autocomplete');
    if ($autocomplete === null) {
        $autocomplete = 'off';
    }

    $isPassword = $type === 'password';
    $baseClass = 'input' . ($size === 'lg' ? ' input--lg' : '');

    $inputAttrs = $attributes
        ->except(['autocomplete'])
        ->merge([
            'id' => $id,
            'name' => $name,
            'autocomplete' => $isPassword ? 'current-password' : $autocomplete,
        ])
        ->class($baseClass);
@endphp

<div class="field">
    @if($label)
        <label for="{{ $id }}" class="field__label">{{ $label }}</label>
    @endif

    @if($isPassword)
        {{-- Поле пароля с кнопкой показать/скрыть --}}
        <div class="input-group" x-data="{ show: false }">
            @if($icon)
                <span class="input-group__addon"><x-ui.icon :name="$icon" :size="16" /></span>
            @endif
            <input
                :type="show ? 'text' : 'password'"
                {{ $inputAttrs->except(['type']) }}
                autocomplete="{{ $isPassword ? 'current-password' : $autocomplete }}"
            />
            <button
                type="button"
                class="input-group__addon input-group__addon--btn"
                @click="show = !show"
                :aria-label="show ? 'Скрыть пароль' : 'Показать пароль'"
                :title="show ? 'Скрыть пароль' : 'Показать пароль'"
                tabindex="-1"
            >
                <x-ui.icon name="eye" :size="16" x-show="!show" />
                <x-ui.icon name="eye-off" :size="16" x-show="show" />
            </button>
        </div>
    @elseif($icon)
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
