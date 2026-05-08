<div>
    @if($open)
        <div class="modal-backdrop" wire:click.self="dismiss" @keydown.escape.window="$wire.dismiss()">
            <div class="modal" role="dialog" aria-modal="true" aria-labelledby="release-notes-title">
                <div class="modal__header">
                    <div class="flex-1">
                        <h2 class="modal__title" id="release-notes-title">{{ $title }}</h2>
                        @if($subtitle !== '')
                            <p class="modal__subtitle">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <button type="button" class="btn btn--ghost btn--icon btn--sm" wire:click="dismiss" aria-label="Закрыть">
                        <x-ui.icon name="x" :size="16" />
                    </button>
                </div>

                <div class="modal__body">
                    @if(count($items) > 0)
                        <ul class="vstack gap-2" style="margin: 0; padding-left: 1.25rem;">
                            @foreach($items as $line)
                                <li>{{ $line }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="modal__footer hstack gap-2" style="justify-content: flex-end;">
                    <x-ui.button variant="primary" wire:click="dismiss" type="button">
                        Понятно
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif
</div>
