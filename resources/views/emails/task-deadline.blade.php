<x-emails.layout
    title="Задачи требуют внимания — Tomodoro"
    preheader="{{ $overdueCount > 0 ? $overdueCount.' просроченных,' : '' }} {{ $todayCount }} на сегодня. Не упусти!"
>
    {{-- ── Header ─────────────────────────────────────── --}}
    <x-slot:header>
        <p style="margin:0 0 10px;font-size:12px;font-weight:600;color:rgba(255,255,255,0.65);text-transform:uppercase;letter-spacing:1.2px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
            Напоминание о задачах
        </p>
        <h1 style="margin:0;font-size:26px;font-weight:700;color:#ffffff;line-height:1.25;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
            🔥 Задачи горят, {{ $name }}!
        </h1>
    </x-slot:header>

    {{-- ── Body ──────────────────────────────────────── --}}

    {{-- Stats row --}}
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
        <tr>
            @if($overdueCount > 0)
            <td style="width:33%;text-align:center;padding:16px 8px;background:#fff1ef;border-radius:12px;border:1px solid #fdd4cb;" align="center">
                <p style="margin:0;font-size:28px;font-weight:700;color:#d04429;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;line-height:1;">{{ $overdueCount }}</p>
                <p style="margin:4px 0 0;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.6px;color:#d04429;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">Просрочено</p>
            </td>
            <td style="width:8px;"></td>
            @endif
            @if($todayCount > 0)
            <td style="{{ $overdueCount > 0 ? 'width:33%;' : 'width:48%;' }}text-align:center;padding:16px 8px;background:#fef9ec;border-radius:12px;border:1px solid #fde68a;" align="center">
                <p style="margin:0;font-size:28px;font-weight:700;color:#cf8a04;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;line-height:1;">{{ $todayCount }}</p>
                <p style="margin:4px 0 0;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.6px;color:#cf8a04;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">Сегодня</p>
            </td>
            @if($tomorrowCount > 0)<td style="width:8px;"></td>@endif
            @endif
            @if($tomorrowCount > 0)
            <td style="text-align:center;padding:16px 8px;background:#f5f4f0;border-radius:12px;border:1px solid #e8e6dd;" align="center">
                <p style="margin:0;font-size:28px;font-weight:700;color:#6c6b62;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;line-height:1;">{{ $tomorrowCount }}</p>
                <p style="margin:4px 0 0;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.6px;color:#9a988d;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">Завтра</p>
            </td>
            @endif
        </tr>
    </table>

    {{-- Task list --}}
    @php
        $priorityConfig = [
            'urgent' => ['label' => 'Срочно',  'bg' => '#fff1ef', 'color' => '#d04429', 'border' => '#fdd4cb'],
            'high'   => ['label' => 'Высокий', 'bg' => '#fff8ec', 'color' => '#c97b1a', 'border' => '#fde4a0'],
            'normal' => ['label' => 'Обычный', 'bg' => '#f5f4f0', 'color' => '#6c6b62', 'border' => '#e8e6dd'],
            'low'    => ['label' => 'Низкий',  'bg' => '#f0f4ff', 'color' => '#4a6fc7', 'border' => '#c7d6fa'],
        ];
    @endphp

    @foreach(['overdue' => '🔴 Просроченные', 'today' => '🟠 На сегодня', 'tomorrow' => '🟡 Завтра'] as $group => $groupLabel)
        @if(!empty($tasks[$group]))
        <p style="margin:0 0 8px;font-size:13px;font-weight:600;color:#9a988d;text-transform:uppercase;letter-spacing:0.8px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;" class="email-muted">
            {{ $groupLabel }}
        </p>
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:20px;border-radius:12px;overflow:hidden;border:1px solid #eeece4;" class="email-divider">
            @foreach($tasks[$group] as $i => $task)
            @php $pc = $priorityConfig[$task['priority']] ?? $priorityConfig['normal']; @endphp
            <tr>
                <td style="padding:13px 16px;{{ $i > 0 ? 'border-top:1px solid #eeece4;' : '' }}background:#ffffff;" class="email-card email-divider">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td style="vertical-align:middle;">
                                <p style="margin:0 0 3px;font-size:14px;font-weight:500;color:#1d1d1a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;" class="email-body-text">
                                    {{ $task['title'] }}
                                </p>
                                @if($task['project'])
                                <p style="margin:0;font-size:12px;color:#9a988d;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;" class="email-muted">
                                    {{ $task['project'] }}
                                </p>
                                @endif
                            </td>
                            <td style="vertical-align:middle;text-align:right;white-space:nowrap;padding-left:12px;">
                                <span style="display:inline-block;padding:3px 9px;border-radius:6px;font-size:11px;font-weight:600;background:{{ $pc['bg'] }};color:{{ $pc['color'] }};border:1px solid {{ $pc['border'] }};font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
                                    {{ $pc['label'] }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            @endforeach
        </table>
        @endif
    @endforeach

    {{-- CTA --}}
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top:8px;">
        <tr>
            <td align="center">
                <a href="{{ config('app.url') }}/app/today"
                   target="_blank"
                   style="display:inline-block;background:#e5533a;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;padding:14px 36px;border-radius:10px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;letter-spacing:-0.1px;">
                    Перейти к задачам →
                </a>
            </td>
        </tr>
    </table>

</x-emails.layout>
