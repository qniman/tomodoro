@props([
    'align' => 'right', // left | right
    'direction' => 'auto', // up | down | auto
    'width' => 220,
])

<div
    class="dropdown"
    x-data="portalDropdown({ align: @js($align), direction: @js($direction), minWidth: {{ (int) $width }} })"
    @keydown.escape.window="close()"
>
    <div x-ref="trigger" @click.stop.prevent="toggle()">
        {{ $trigger }}
    </div>

    <template x-teleport="body">
        <div
            x-show="open"
            x-ref="portalMenu"
            x-cloak
            x-transition.opacity.duration.120ms
            class="dropdown__menu dropdown__menu--portal"
            :style="menuStyle"
            @click="close()"
        >
            {{ $slot }}
        </div>
    </template>
</div>
