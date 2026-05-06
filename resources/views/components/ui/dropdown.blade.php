@props([
    'align' => 'right',         // left | right
    'direction' => 'auto',      // up | down | auto
    'width' => 220,
])

@php
    $directionClass = match ($direction) {
        'up' => 'dropdown__menu--up',
        'down' => '',
        default => '',
    };
@endphp

<div
    class="dropdown"
    x-data="{
        open: false,
        direction: @js($direction),
        directionClass: '',
        toggle() {
            this.open = !this.open;
            if (this.open) this.$nextTick(() => this.recalc());
        },
        recalc() {
            if (this.direction !== 'auto') {
                this.directionClass = this.direction === 'up' ? 'dropdown__menu--up' : '';
                return;
            }
            const trigger = this.$refs.trigger;
            const menu = this.$refs.menu;
            if (! trigger || ! menu) return;
            const triggerRect = trigger.getBoundingClientRect();
            const menuHeight = menu.offsetHeight || 240;
            const spaceBelow = window.innerHeight - triggerRect.bottom;
            const spaceAbove = triggerRect.top;
            this.directionClass = (spaceBelow < menuHeight + 24 && spaceAbove > spaceBelow)
                ? 'dropdown__menu--up'
                : '';
        },
    }"
    x-init="directionClass = @js($directionClass)"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    @resize.window="open && recalc()"
>
    <div x-ref="trigger" @click="toggle()">
        {{ $trigger }}
    </div>

    <div
        x-ref="menu"
        x-show="open"
        x-transition.opacity.duration.120ms
        class="dropdown__menu {{ $align === 'left' ? 'dropdown__menu--left' : '' }}"
        :class="directionClass"
        style="min-width: {{ $width }}px"
        @click="open = false"
        x-cloak
    >
        {{ $slot }}
    </div>
</div>
