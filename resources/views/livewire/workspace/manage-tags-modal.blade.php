<div>
    @if($embedded)
        <div class="manage-tags-embedded vstack gap-4">
            @include('livewire.workspace.manage-tags-fields')
            @if($viewMode === 'form')
                <div class="hstack gap-2" style="justify-content: flex-end; padding-top: var(--s-2); border-top: 1px solid var(--border);">
                    <x-ui.button variant="ghost" wire:click="cancelForm">Отмена</x-ui.button>
                    <x-ui.button variant="primary" icon="check" wire:click="save">Сохранить</x-ui.button>
                </div>
            @endif
        </div>
    @elseif($open)
        <div class="modal-backdrop" wire:click.self="close" @keydown.escape.window="$wire.close()">
            <div class="modal modal--lg" role="dialog" aria-modal="true">
                <div class="modal__header">
                    <div class="flex-1">
                        <h2 class="modal__title">
                            {{ $viewMode === 'form' ? ($editingId ? 'Тег' : 'Новый тег') : 'Теги' }}
                        </h2>
                        @if($viewMode === 'list')
                            <p class="modal__subtitle">Метки для фильтров и задач: имя, цвет и значок.</p>
                        @endif
                    </div>
                    <button type="button" class="btn btn--ghost btn--icon btn--sm" wire:click="close" aria-label="Закрыть">
                        <x-ui.icon name="x" :size="16" />
                    </button>
                </div>

                <div class="modal__body">
                    @include('livewire.workspace.manage-tags-fields')
                </div>

                @if($viewMode === 'form')
                    <div class="modal__footer hstack gap-2" style="justify-content: flex-end;">
                        <x-ui.button variant="ghost" wire:click="cancelForm">Отмена</x-ui.button>
                        <x-ui.button variant="primary" icon="check" wire:click="save">Сохранить</x-ui.button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
