<div class="space-y-6">
    <div class="flex flex-wrap gap-3">
        <button class="btn-primary" wire:click="openCreateTask">Новая задача</button>
        <!-- Создание тегов и категорий перенесено в Предустановки -->
    </div>

    <div class="panel space-y-4">
        <div class="grid gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label class="filter-label">Поиск</label>
                <input wire:model.live.debounce-300ms="filterDraft.search" type="search" placeholder="Название или тег" class="filter-input" />
            </div>
            <div>
                <label class="filter-label">Приоритет</label>
                <select wire:model.live.debounce-300ms="filterDraft.priority" class="filter-input">
                    <option value="">Любой</option>
                    @foreach($priorityOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="filter-label">Категория</label>
                <select wire:model.live.debounce-300ms="filterDraft.category" class="filter-input">
                    <option value="">Все</option>
                    @foreach($categoryOptions as $categoryOption)
                        <option value="{{ $categoryOption->name }}">{{ $categoryOption->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Status filter tabs with horizontal scroll -->
        <div class="space-y-2">
            <label class="filter-label">Статус</label>
            <div class="flex gap-2 overflow-x-auto pb-2 -mx-2 px-2">
                <button 
                    wire:click="setStatusFilter(null)" 
                    class="px-4 py-2 rounded-md text-sm font-medium whitespace-nowrap transition
                    {{ $selectedStatusFilter === null ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Все
                </button>
                @foreach($statusOptions as $statusOption)
                    @php
                        $statusColor = $statusOption->color ?? '#94a3b8';
                    @endphp
                    <button 
                        wire:click="setStatusFilter('{{ $statusOption->name }}')" 
                        class="px-4 py-2 rounded-md text-sm font-medium whitespace-nowrap transition"
                        style="
                            background-color: {{ $selectedStatusFilter === $statusOption->name ? $statusColor : $statusColor . '20' }};
                            color: {{ $selectedStatusFilter === $statusOption->name ? 'white' : $statusColor }};
                            border: 1px solid {{ $statusColor }};
                        ">
                        {{ $statusOption->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="button" class="btn-secondary text-xs" wire:click="resetFilters">Очистить</button>
        </div>
    </div>

    @php
        $categoryMap = $categoryOptions->keyBy('name');
        $statusMap = $statusOptions->keyBy('name');
    @endphp

    <div class="space-y-3">
        @forelse($tasks as $task)
            @php
                $categoryColor = optional($categoryMap->get($task->category))->color ?? '#6366f1';
                $statusColor = optional($statusMap->get($task->status))->color ?? '#94a3b8';
            @endphp
            <article class="rounded-md border border-slate-200 shadow-xl shadow-slate-100 bg-white px-4 py-4 flex flex-col lg:flex-row lg:items-center justify-between gap-1">
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs uppercase tracking-wide" style="border-color: {{ $categoryColor }}; color: {{ $categoryColor }}">
                            <span class="w-2 h-2 rounded-full" style="background: {{ $categoryColor }}"></span>
                            {{ $task->category ?? 'Без категории' }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs uppercase tracking-wide" style="border-color: {{ $statusColor }}; color: {{ $statusColor }}">
                            <span class="w-2 h-2 rounded-full" style="background: {{ $statusColor }}"></span>
                            {{ $task->status }}
                        </span>
                        <span class="rounded-full px-3 py-1 text-xs uppercase tracking-wide
                                     {{ $task->priority === 'high' ? 'bg-red-50 border-[1px] border-red-300 text-red-400' : ($task->priority === 'medium' ? 'bg-amber-50 border-[1px] border-amber-300 text-amber-400' : 'bg-emerald-50 border-[1px] border-emerald-300 text-emerald-400') }}">
                            {{ $priorityOptions[$task->priority] ?? ucfirst($task->priority) }}
                        </span>
                    </div>
                    <h3 class="text-lg font-semibold mt-3">{{ $task->title }}</h3>
                    <p class="text-sm text-slate-400 mt-1">{{ $task->description ?: 'Описание отсутствует' }}</p>
                    <div class="text-xs text-slate-500 mt-2 space-x-3">
                        <span>Дедлайн: {{ optional($task->due_at)->format('d.m.Y H:i') ?? 'не задан' }}</span>
                        @if(!empty($task->actual_minutes))
                            <span>Потрачено: {{ $task->actual_minutes }} мин</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-1 mt-3 text-xs">
                        @forelse($task->tags as $tag)
                            <span class="px-2 py-1 rounded-full border text-[10px]" style="border-color: {{ $tag->color }}; color: {{ $tag->color }}">
                                {{ $tag->name }}
                            </span>
                        @empty
                            <span class="text-slate-500 text-xs">Тегов нет</span>
                        @endforelse
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <button wire:click="editTask({{ $task->id }})" class="btn-secondary">Редактировать</button>
                    <button wire:click="requestDeleteTask({{ $task->id }})" class="btn-secondary text-red-400 bg-red-50 border-red-500/40">Удалить</button>
                </div>
            </article>
        @empty
            <p class="text-sm text-slate-400">Список пуст. Добавьте первую задачу.</p>
        @endforelse
    </div>

    {{-- Модал: задача --}}
    @if($showTaskModal)
        <div class="modal-overlay" wire:key="task-modal">
            <div class="modal-panel max-w-3xl">
                <div class="modal-header">
                    <h3>{{ $isEditing ? 'Редактирование задачи' : 'Новая задача' }}</h3>
                    <button type="button" class="modal-close" wire:click="$set('showTaskModal', false)">×</button>
                </div>
                <form wire:submit.prevent="saveTask" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="filter-label">Название</label>
                        <input wire:model.defer="taskForm.title" type="text" class="filter-input" />
                        @error('taskForm.title') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="filter-label">Категория</label>
                        <select wire:model.defer="taskForm.category" class="filter-input">
                            <option value="">Без категории</option>
                            @foreach($categoryOptions as $categoryOption)
                                <option value="{{ $categoryOption->name }}">{{ $categoryOption->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Статус: доступен только при редактировании --}}
                    @if($isEditing)
                        <div>
                            <label class="filter-label">Статус</label>
                            <select wire:model.defer="taskForm.status" class="filter-input">
                                @foreach($statusOptions as $statusOption)
                                    <option value="{{ $statusOption->name }}">{{ $statusOption->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label class="filter-label">Приоритет</label>
                        <select wire:model.defer="taskForm.priority" class="filter-input">
                            <option value="low">Низкий</option>
                            <option value="medium">Средний</option>
                            <option value="high">Высокий</option>
                        </select>
                    </div>
                    <div>
                        <label class="filter-label">Дедлайн</label>
                        <input wire:model.defer="taskForm.due_at" type="datetime-local" class="filter-input" />
                    </div>
                    {{-- Оценка и факт вычисляются автоматически, поля скрыты --}}
                    <div>
                        <label class="filter-label">Дата создания</label>
                        <input wire:model.defer="taskForm.created_at" type="datetime-local" class="filter-input" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="filter-label">Описание</label>
                        <textarea wire:model.defer="taskForm.description" rows="3" class="filter-input"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="filter-label">Теги</label>
                        <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto p-2 border border-slate-200 rounded-md">
                            @foreach($tags as $tag)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model="selectedTagIds" value="{{ $tag->id }}" class="form-checkbox" />
                                    <span class="w-3 h-3 rounded-full" style="background: {{ $tag->color }}"></span>
                                    <span>{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="md:col-span-2 flex justify-end gap-3 mt-2">
                        <button type="button" class="btn-secondary" wire:click="$set('showTaskModal', false)">Отмена</button>
                        <button type="submit" class="btn-primary">{{ $isEditing ? 'Сохранить' : 'Создать' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Модал: удаление --}}
    @if($showDeleteModal)
        <div class="modal-overlay" wire:key="task-delete-modal">
            <div class="modal-panel max-w-md">
                <div class="modal-header">
                    <h3>Удалить задачу</h3>
                    <button type="button" class="modal-close" wire:click="$set('showDeleteModal', false)">×</button>
                </div>
                <p class="text-sm text-slate-300">Действие нельзя отменить. Продолжить?</p>
                <div class="flex justify-end gap-3 mt-6">
                    <button class="btn-secondary" wire:click="$set('showDeleteModal', false)">Отмена</button>
                    <button class="btn-primary bg-red-500 hover:bg-red-400 border-red-400/40" wire:click="deleteTaskConfirmed">Удалить</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Статусы: управление вынесено в Предустановки (PresetManager) --}}
</div>
