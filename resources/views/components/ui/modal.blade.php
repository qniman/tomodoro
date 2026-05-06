@props([
    'show' => false,
    'closeAction' => null,    // например 'closeTaskModal' или '$set(\'showModal\', false)'
    'size' => 'md',
    'title' => null,
    'subtitle' => null,
])

@php
    $sizeClass = match ($size) {
        'lg' => 'modal--lg',
        'xl' => 'modal--xl',
        default => '',
    };
@endphp

@if($show)
    <div
        class="modal-backdrop"
        x-data
        wire:click.self="{{ $closeAction }}"
        @keydown.escape.window="$wire.call('{{ $closeAction }}')"
    >
        <div class="modal {{ $sizeClass }}" role="dialog" aria-modal="true" {{ $attributes }}>
            @if($title || $subtitle)
                <div class="modal__header">
                    <div class="flex-1">
                        @if($title)
                            <h2 class="modal__title">{{ $title }}</h2>
                        @endif
                        @if($subtitle)
                            <p class="modal__subtitle">{{ $subtitle }}</p>
                        @endif
                    </div>
                    @if($closeAction)
                        <button
                            type="button"
                            class="btn btn--ghost btn--icon btn--sm"
                            wire:click="{{ $closeAction }}"
                            aria-label="Закрыть"
                        >
                            <x-ui.icon name="x" :size="16" />
                        </button>
                    @endif
                </div>
            @endif

            <div class="modal__body">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="modal__footer">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
@endif
