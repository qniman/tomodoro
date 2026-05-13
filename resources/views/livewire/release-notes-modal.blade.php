<div>
    @if($open)
        <div
            class="modal-backdrop"
            wire:click.self="dismiss"
            @keydown.escape.window="$wire.dismiss()"
        >
            <div class="modal modal--lg changelog-modal" role="dialog" aria-modal="true" aria-labelledby="changelog-title">
                <div class="modal__header">
                    <div class="flex-1">
                        <h2 class="modal__title" id="changelog-title">Что нового</h2>
                        <p class="modal__subtitle">История обновлений Tomodoro</p>
                    </div>
                    <button
                        type="button"
                        class="btn btn--ghost btn--icon btn--sm"
                        wire:click="dismiss"
                        aria-label="Закрыть"
                    >
                        <x-ui.icon name="x" :size="16" />
                    </button>
                </div>

                <div class="modal__body changelog-feed">
                    @foreach($releases as $release)
                        @php
                            $date = \Carbon\Carbon::parse($release['date'])->locale('ru')->isoFormat('D MMMM YYYY');
                            $hasNew      = !empty($release['new']);
                            $hasImproved = !empty($release['improved']);
                            $hasFixed    = !empty($release['fixed']);
                        @endphp

                        <div class="changelog-release {{ $release['is_new'] ? 'changelog-release--unread' : '' }}">
                            <div class="changelog-release__meta">
                                <span class="changelog-release__version">v{{ $release['version'] }}</span>
                                @if($release['is_new'])
                                    <span class="badge badge--accent" style="font-size: 10px; padding: 1px 6px;">Новое</span>
                                @endif
                                <span class="changelog-release__date">{{ $date }}</span>
                            </div>

                            <h3 class="changelog-release__title">{{ $release['title'] }}</h3>

                            @if($hasNew)
                                <div class="changelog-section">
                                    <div class="changelog-section__label changelog-section__label--new">
                                        <x-ui.icon name="sparkles" :size="13" />
                                        Новое
                                    </div>
                                    <ul class="changelog-list">
                                        @foreach($release['new'] as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($hasImproved)
                                <div class="changelog-section">
                                    <div class="changelog-section__label changelog-section__label--improved">
                                        <x-ui.icon name="star" :size="13" />
                                        Улучшено
                                    </div>
                                    <ul class="changelog-list">
                                        @foreach($release['improved'] as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($hasFixed)
                                <div class="changelog-section">
                                    <div class="changelog-section__label changelog-section__label--fixed">
                                        <x-ui.icon name="check-circle" :size="13" />
                                        Исправлено
                                    </div>
                                    <ul class="changelog-list">
                                        @foreach($release['fixed'] as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(!empty($release['notice']))
                                <div class="changelog-notice">
                                    <x-ui.icon name="info" :size="13" />
                                    {{ $release['notice'] }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="modal__footer">
                    <x-ui.button variant="primary" wire:click="dismiss" type="button">
                        Понятно
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif
</div>
