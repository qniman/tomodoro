@auth
<div
    x-data="cmdPalette()"
    x-init="init()"
    x-cloak
    @open-cmdk.window="openWith('')"
>
    <template x-if="open">
        <div class="cmdk" @click.self="close" @keydown.escape.window.prevent="close">
            <div class="cmdk__panel" @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)" @keydown.enter.prevent="commit()">
                <div class="cmdk__search">
                    <span style="color: var(--text-subtle);">
                        <x-ui.icon name="search" :size="16" />
                    </span>
                    <input
                        x-ref="input"
                        x-model="query"
                        type="text"
                        placeholder="Поиск действий и разделов..."
                        @input="highlight = 0"
                        autocomplete="off"
                        spellcheck="false"
                    />
                    <kbd class="kbd">Esc</kbd>
                </div>

                <div class="cmdk__list">
                    <template x-for="(item, i) in items" :key="(item.kind === 'group' ? 'g-' + item.label : item.id)">
                        <div>
                            <template x-if="item.kind === 'group'">
                                <div class="cmdk__group" x-text="item.label"></div>
                            </template>
                            <template x-if="item.kind !== 'group'">
                                <button type="button" class="cmdk__item" :class="{ 'is-active': isActive(item) }" @click="runItem(item)">
                                    <span class="cmdk__icon" x-text="item.icon"></span>
                                    <div class="flex-1">
                                        <div class="cmdk__title" x-text="item.title"></div>
                                        <div class="cmdk__sub" x-text="item.hint" x-show="item.hint"></div>
                                    </div>
                                    <span class="cmdk__keys" x-show="item.keys">
                                        <template x-for="k in item.keys.split(' ')" :key="k">
                                            <kbd class="kbd" x-text="k"></kbd>
                                        </template>
                                    </span>
                                </button>
                            </template>
                        </div>
                    </template>

                    <template x-if="selectableItems.length === 0">
                        <div style="padding: var(--s-6); text-align: center; color: var(--text-subtle);">
                            Ничего не нашлось. Попробуй другие слова.
                        </div>
                    </template>
                </div>

                <div class="cmdk__footer">
                    <span>Подсказки: <kbd class="kbd">g</kbd> + клавиша — переход</span>
                    <span class="cmdk__footer-keys">
                        <span><kbd class="kbd">↑</kbd><kbd class="kbd">↓</kbd> двигаться</span>
                        <span><kbd class="kbd">Enter</kbd> выбрать</span>
                    </span>
                </div>
            </div>
        </div>
    </template>
</div>
@endauth
