@props([
    'name' => 'square',
    'size' => 18,
])

@php
    /**
     * Локально хранимый набор Lucide-style иконок.
     * Все они используют единый шаблон stroke=currentColor, чтобы работать с любым цветом текста.
     */
    $paths = [
        // Навигация
        'home'       => '<path d="M3 11.5L12 4l9 7.5"/><path d="M5 10v10h14V10"/>',
        'inbox'      => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5.5L4 12v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-6l-1.5-6.5A2 2 0 0 0 16.6 4H7.4a2 2 0 0 0-1.9 1.5z"/>',
        'calendar'   => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'sun-medium' => '<circle cx="12" cy="12" r="4"/><path d="M12 3v1M12 20v1M5.6 5.6l.7.7M17.7 17.7l.7.7M3 12h1M20 12h1M5.6 18.4l.7-.7M17.7 6.3l.7-.7"/>',
        'today'      => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><circle cx="12" cy="15" r="2.5" fill="currentColor" stroke="none"/>',
        'folder'     => '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
        'tag'        => '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0l-7.2-7.2a2 2 0 0 1-.6-1.4V5a2 2 0 0 1 2-2h7a2 2 0 0 1 1.4.6l7.2 7.2a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.2" fill="currentColor" stroke="none"/>',
        'settings'   => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
        'search'     => '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>',
        'menu'       => '<path d="M4 6h16M4 12h16M4 18h16"/>',

        // Действия
        'plus'       => '<path d="M12 5v14M5 12h14"/>',
        'minus'      => '<path d="M5 12h14"/>',
        'x'          => '<path d="M18 6 6 18M6 6l12 12"/>',
        'trash'      => '<path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M10 11v6M14 11v6"/>',
        'edit'       => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"/>',
        'pencil'     => '<path d="M21.2 5.8 18.2 2.8 5 16 4 20l4-1 13.2-13.2z"/>',
        'more-h'     => '<circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/>',
        'more-v'     => '<circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>',
        'check'      => '<path d="M20 6 9 17l-5-5"/>',
        'check-2'    => '<path d="M5 12l5 5L20 7"/>',

        // Стрелки и шевроны
        'chevron-down'   => '<path d="m6 9 6 6 6-6"/>',
        'chevron-up'     => '<path d="m6 15 6-6 6 6"/>',
        'chevron-left'   => '<path d="m15 18-6-6 6-6"/>',
        'chevron-right'  => '<path d="m9 18 6-6-6-6"/>',
        'arrow-left'     => '<path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>',
        'arrow-right'    => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',

        // Задачи
        'flag'       => '<path d="M4 22V4"/><path d="M4 4h12l-2 4 2 4H4"/>',
        'paperclip'  => '<path d="m21 12-9 9a5 5 0 0 1-7-7l9-9a3 3 0 0 1 4 4l-9 9a1 1 0 1 1-1-1l8-8"/>',
        'clock'      => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'alarm'      => '<circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2"/><path d="M5 3 2 6M19 3l3 3"/>',
        'bell'       => '<path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10 21a2 2 0 0 0 4 0"/>',
        'list-todo'  => '<rect x="3" y="5" width="6" height="6" rx="1"/><path d="m4 8 1 1 2-2"/><path d="M11 6h10M11 12h10M11 18h10"/><rect x="3" y="15" width="6" height="6" rx="1"/>',
        'square'     => '<rect x="3" y="3" width="18" height="18" rx="2"/>',
        'square-check'=> '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="m9 12 2 2 4-4"/>',

        // Помодоро
        'timer'      => '<path d="M10 2h4"/><circle cx="12" cy="14" r="8"/><path d="M12 10v4l3 2"/>',
        'play'       => '<polygon points="6 4 20 12 6 20" fill="currentColor" stroke="none"/>',
        'pause'      => '<rect x="6" y="4" width="4" height="16" rx="1" fill="currentColor" stroke="none"/><rect x="14" y="4" width="4" height="16" rx="1" fill="currentColor" stroke="none"/>',
        'stop'       => '<rect x="5" y="5" width="14" height="14" rx="2" fill="currentColor" stroke="none"/>',
        'fast-fwd'   => '<polygon points="13 19 22 12 13 5"/><polygon points="2 19 11 12 2 5"/>',
        'refresh'    => '<path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/>',
        'tomato'     => '<path d="M12 5c4 0 7 3 7 7s-3 7-7 7-7-3-7-7 3-7 7-7z"/><path d="M9 5c1-1.5 2-2 3-2s2 .5 3 2"/>',

        // Темы и режим
        'sun'        => '<circle cx="12" cy="12" r="4"/><path d="M12 3v1M12 20v1M5.6 5.6l.7.7M17.7 17.7l.7.7M3 12h1M20 12h1M5.6 18.4l.7-.7M17.7 6.3l.7-.7"/>',
        'moon'       => '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>',
        'monitor'    => '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>',

        // Профиль и сессия
        'user'       => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'log-out'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
        'mail'       => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
        'lock'       => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 1 1 8 0v4"/>',
        'key'        => '<circle cx="7.5" cy="15.5" r="3.5"/><path d="m11 13 9-9"/><path d="m17 7 3 3"/>',

        // Контент
        'eye'        => '<path d="M2 12s4-8 10-8 10 8 10 8-4 8-10 8-10-8-10-8z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off'    => '<path d="M9.9 4.2A10 10 0 0 1 12 4c6 0 10 8 10 8a18 18 0 0 1-3 3.8M6.7 6.7A18 18 0 0 0 2 12s4 8 10 8a10 10 0 0 0 4.2-.9"/><path d="m1 1 22 22"/><path d="M14.1 14a3 3 0 0 1-4.1-4.1"/>',
        'image'      => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/>',
        'file'       => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
        'file-text'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h6"/>',
        'link'       => '<path d="M10 13a5 5 0 0 0 7.5.6l3-3a5 5 0 0 0-7.1-7.1l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.6l-3 3a5 5 0 0 0 7.1 7.1l1.7-1.7"/>',
        'upload'     => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/>',
        'sparkles'   => '<path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2 2M16.4 16.4l2 2M5.6 18.4l2-2M16.4 7.6l2-2"/>',
        'star'       => '<polygon points="12 2 15 9 22 10 17 15 18 22 12 18 6 22 7 15 2 10 9 9"/>',
        'pin'        => '<path d="M12 17v5"/><path d="M9 10.8 6 13.6V17h12v-3.4l-3-2.8V4H9z"/>',

        // Состояния
        'info'         => '<circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/>',
        'alert-circle' => '<circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/>',
        'alert-triangle' => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/>',
        'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="m9 12 2 2 4-4"/>',
        'x-circle'     => '<circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/>',

        // Редактор
        'bold'         => '<path d="M7 4h6a4 4 0 0 1 0 8H7zM7 12h7a4 4 0 0 1 0 8H7z"/>',
        'italic'       => '<path d="M19 4h-9M14 20H5M15 4 9 20"/>',
        'strike'       => '<path d="M16 4H9a3 3 0 0 0-2.8 4M14 12a4 4 0 0 1 0 8H6"/><path d="M4 12h16"/>',
        'list'         => '<path d="M8 6h13M8 12h13M8 18h13"/><circle cx="4" cy="6" r="1"/><circle cx="4" cy="12" r="1"/><circle cx="4" cy="18" r="1"/>',
        'list-ord'     => '<path d="M10 6h11M10 12h11M10 18h11"/><path d="M4 6h1v4M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/>',
        'quote'        => '<path d="M3 21c3 0 7-1 7-8V5H3v7h4c0 4-1 6-4 6zM14 21c3 0 7-1 7-8V5h-7v7h4c0 4-1 6-4 6z"/>',
        'code'         => '<path d="m16 18 6-6-6-6M8 6l-6 6 6 6"/>',
        'h1'           => '<path d="M4 12h12M4 6v12M16 6v12"/><path d="M21 6h-3v6h3"/>',
        'h2'           => '<path d="M4 12h12M4 6v12M16 6v12"/><path d="M18 18h4M18 18a2 2 0 1 1 4 0c0 1-1 2-2 3l-2 1"/>',
        'undo'         => '<path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-15-6.7L3 13"/>',
        'redo'         => '<path d="M21 7v6h-6"/><path d="M3 17a9 9 0 0 1 15-6.7L21 13"/>',

        // Календарь
        'cal-grid'     => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M9 14h.01M13 14h.01M17 14h.01M9 18h.01M13 18h.01M17 18h.01"/>',
        'cal-week'     => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M3 16h18"/>',
        'cal-year'     => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M9 16h6"/>',

        // Настройки
        'palette'      => '<circle cx="13.5" cy="6.5" r="1.5" fill="currentColor"/><circle cx="17.5" cy="10.5" r="1.5" fill="currentColor"/><circle cx="8.5" cy="7.5" r="1.5" fill="currentColor"/><circle cx="6.5" cy="12.5" r="1.5" fill="currentColor"/><path d="M12 22a10 10 0 1 1 10-10c0 2-1.5 3-3 3h-2a2 2 0 0 0-1 4 2 2 0 0 1-1 3c-1 0-3 0-3 0z"/>',
        'shield'       => '<path d="M12 3 4 6v6c0 5 4 8 8 9 4-1 8-4 8-9V6z"/>',
        'rotate-ccw'   => '<path d="M3 12a9 9 0 1 0 9-9c-2.5 0-4.7 1-6.4 2.6L3 8"/><path d="M3 3v5h5"/>',
        'command'      => '<path d="M18 3a3 3 0 0 0-3 3v3h3a3 3 0 1 0 0-6zM6 3a3 3 0 0 1 3 3v3H6a3 3 0 1 1 0-6zM18 21a3 3 0 0 0-3-3v-3h3a3 3 0 1 1 0 6zM6 21a3 3 0 0 1 3-3v-3H6a3 3 0 1 0 0 6zM9 9h6v6H9z"/>',
    ];

    $svg = $paths[$name] ?? $paths['square'];
    $size = (int) $size;
@endphp

<svg
    xmlns="http://www.w3.org/2000/svg"
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.75"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    {{ $attributes }}
>
    {!! $svg !!}
</svg>
