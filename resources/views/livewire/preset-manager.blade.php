<div class="space-y-10">
    <section>
        <div class="flex items-center justify-between mb-3">
            <h3 class="panel-title text-base">Теги</h3>
            <button class="btn-secondary text-xs" wire:click="openModal('tag')">Добавить</button>
        </div>
        <div class="overflow-x-auto rounded-md border border-gray-300">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-300 text-black uppercase text-xs">
                    <tr>
                        <th class="px-4 py-2 text-left">Название</th>
                        <th class="px-4 py-2 text-left">Цвет</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tags as $tag)
                        <tr class="border-t border-slate-800">
                            <td class="px-4 py-2">{{ $tag->name }}</td>
                            <td class="px-4 py-2">
                                <span class="inline-flex items-center gap-2">
                                    <span class="w-4 h-4 rounded-full" style="background: {{ $tag->color }}"></span>
                                    {{ $tag->color }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <button class="btn-secondary text-xs" wire:click="editTag({{ $tag->id }})">Изменить</button>
                                <button class="btn-secondary text-xs text-red-300 border-red-400/40" wire:click="deleteTag({{ $tag->id }})">Удалить</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-3 text-slate-500" colspan="3">Тегов нет.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <div class="flex items-center justify-between mb-3">
            <h3 class="panel-title text-base">Категории</h3>
            <button class="btn-secondary text-xs" wire:click="openModal('category')">Добавить</button>
        </div>
        <div class="overflow-x-auto rounded-md border border-gray-300">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-300 text-black uppercase text-xs">
                    <tr>
                        <th class="px-4 py-2 text-left">Название</th>
                        <th class="px-4 py-2 text-left">Цвет</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr class="border-t border-gray-300">
                            <td class="px-4 py-2">{{ $category->name }}</td>
                            <td class="px-4 py-2">
                                <span class="inline-flex items-center gap-2">
                                    <span class="w-4 h-4 rounded-full" style="background: {{ $category->color }}"></span>
                                    {{ $category->color }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <button class="btn-secondary text-xs" wire:click="editCategory({{ $category->id }})">Изменить</button>
                                <button class="btn-secondary text-xs bg-red-50 text-red-400 border-red-400/40" wire:click="deleteCategory({{ $category->id }})">Удалить</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-3 text-slate-500" colspan="3">Категорий нет.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <div class="flex items-center justify-between mb-3">
            <h3 class="panel-title text-base">Статусы</h3>
            <span class="text-xs text-slate-500">Нельзя добавлять/удалять статусы — используются предопределённые</span>
        </div>
        <div class="overflow-x-auto rounded-md border border-gray-300">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-300 text-black uppercase text-xs">
                    <tr>
                        <th class="px-4 py-2 text-left">Название</th>
                        <th class="px-4 py-2 text-left">Цвет</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statuses as $status)
                        <tr class="border-t border-gray-300">
                            <td class="px-4 py-2">{{ $status->name }}</td>
                            <td class="px-4 py-2">
                                <span class="inline-flex items-center gap-2">
                                    <span class="w-4 h-4 rounded-full" style="background: {{ $status->color }}"></span>
                                    {{ $status->color }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right text-slate-500">&nbsp;</td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-3 text-slate-500" colspan="3">Статусов нет.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Тег --}}
    @if($showTagModal)
        <div class="modal-overlay">
            <div class="modal-panel max-w-md">
                <div class="modal-header">
                    <h3>{{ $editingType === 'tag' && $editingId ? 'Изменить тег' : 'Новый тег' }}</h3>
                    <button type="button" class="modal-close" wire:click="resetTagForm">×</button>
                </div>
                <form wire:submit.prevent="createTag" class="space-y-4">
                    <div>
                        <label class="filter-label">Название</label>
                        <input wire:model.defer="tagForm.name" type="text" class="filter-input" />
                    </div>
                    <div>
                        <label class="filter-label">Цвет</label>
                        <input wire:model.defer="tagForm.color" type="color" class="filter-input h-12" />
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" class="btn-secondary" wire:click="resetTagForm">Отмена</button>
                        <button type="submit" class="btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Категория --}}
    @if($showCategoryModal)
        <div class="modal-overlay">
            <div class="modal-panel max-w-md">
                <div class="modal-header">
                    <h3>{{ $editingType === 'category' && $editingId ? 'Изменить категорию' : 'Новая категория' }}</h3>
                    <button type="button" class="modal-close" wire:click="resetCategoryForm">×</button>
                </div>
                <form wire:submit.prevent="createCategory" class="space-y-4">
                    <div>
                        <label class="filter-label">Название</label>
                        <input wire:model.defer="categoryForm.name" type="text" class="filter-input" />
                    </div>
                    <div>
                        <label class="filter-label">Цвет</label>
                        <input wire:model.defer="categoryForm.color" type="color" class="filter-input h-12" />
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" class="btn-secondary" wire:click="resetCategoryForm">Отмена</button>
                        <button type="submit" class="btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Статусы фиксированны и не редактируются через UI --}}
</div>
