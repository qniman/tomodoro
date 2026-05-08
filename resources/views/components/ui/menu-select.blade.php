@props([
    'property' => null,
    'value' => null,
    'options' => [],
    'placeholder' => '—',
    'align' => 'left',
    'minWidth' => 240,
    'direction' => 'auto',
    'triggerClass' => 'menu-select__trigger select',
])

@php
    use Illuminate\Support\Js;
    $currentLabel = $placeholder;
    foreach ($options as $row) {
        if (($row['value'] ?? null) == $value) {
            $currentLabel = $row['label'] ?? (string) ($row['value'] ?? '');
            break;
        }
    }
@endphp

<div
    class="menu-select"
    x-data="portalMenuSelect({ property: @js($property), align: @js($align), direction: @js($direction), minWidth: {{ (int) $minWidth }} })"
    @keydown.escape.window="close()"
>
    <button
        type="button"
        x-ref="trigger"
        class="{{ $triggerClass }}"
        @click.stop.prevent="toggle()"
        aria-haspopup="listbox"
        :aria-expanded="open"
    >
        <span class="menu-select__value">{{ $currentLabel }}</span>
        <x-ui.icon name="chevron-down" :size="14" class="menu-select__chev" />
    </button>

    <template x-teleport="body">
        <div
            x-show="open"
            x-ref="portalMenu"
            x-cloak
            x-transition.opacity.duration.120ms
            class="dropdown__menu dropdown__menu--portal"
            :style="menuStyle"
            role="listbox"
            @click="close()"
        >
            @foreach($options as $row)
                @php
                    $optVal = $row['value'] ?? null;
                    $optLabel = $row['label'] ?? '';
                    $dot = $row['dotColor'] ?? null;
                    $ic = $row['icon'] ?? null;
                    $sel = $optVal == $value;
                @endphp
                <button
                    type="button"
                    class="dropdown__item {{ $sel ? 'is-selected' : '' }}"
                    role="option"
                    aria-selected="{{ $sel ? 'true' : 'false' }}"
                    @click.stop.prevent="pick({{ Js::from($optVal) }})"
                >
                    @if($dot)
                        <span class="sidebar__project-dot" style="background: {{ $dot }}"></span>
                    @endif
                    @if($ic)
                        <span style="color: {{ $row['iconColor'] ?? 'var(--text-subtle)' }}">
                            <x-ui.icon :name="$ic" :size="16" />
                        </span>
                    @endif
                    <span style="flex: 1 1 auto; min-width: 0">{{ $optLabel }}</span>
                    @if($sel)
                        <x-ui.icon name="check" :size="14" />
                    @endif
                </button>
            @endforeach
        </div>
    </template>
</div>
