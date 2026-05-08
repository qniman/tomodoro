<?php

namespace App\Support;

/**
 * Поддерживаемые иконки интерфейса (ключи см. components/ui/icon.blade.php).
 *
 * @return array<string, string>
 */
final class UiIconSet
{
    public static function choices(): array
    {
        return [
            'folder' => 'Папка',
            'inbox' => 'Входящие',
            'today' => 'Сегодня',
            'calendar' => 'Календарь',
            'tag' => 'Метка',
            'timer' => 'Таймер',
            'tomato' => 'Помодоро',
            'star' => 'Звезда',
            'flag' => 'Флаг',
            'pin' => 'Закладка',
            'list-todo' => 'Список',
            'file-text' => 'Документ',
            'image' => 'Изображение',
            'palette' => 'Цвет',
            'sparkles' => 'Идеи',
            'user' => 'Личное',
            'square' => 'Квадрат',
        ];
    }
}
