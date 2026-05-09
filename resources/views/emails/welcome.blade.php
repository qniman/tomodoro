<x-emails.layout
    title="Добро пожаловать в Tomodoro"
    preheader="Рады видеть вас! Ваш аккаунт создан и готов к работе."
>
    {{-- ── Header ─────────────────────────────────────── --}}
    <x-slot:header>
        <p style="margin:0 0 10px;font-size:12px;font-weight:600;color:rgba(255,255,255,0.65);text-transform:uppercase;letter-spacing:1.2px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
            Добро пожаловать
        </p>
        <h1 style="margin:0;font-size:26px;font-weight:700;color:#ffffff;line-height:1.25;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
            Привет, {{ $name }}! 🎉
        </h1>
    </x-slot:header>

    {{-- ── Body ──────────────────────────────────────── --}}
    <p class="email-body-text" style="margin:0 0 20px;font-size:15px;line-height:1.7;color:#1d1d1a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
        Ваш аккаунт в <strong>Tomodoro</strong> успешно создан. Теперь вы можете планировать задачи, запускать помодоро-таймер и фокусироваться на том, что важно.
    </p>

    {{-- Feature highlights --}}
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:32px;">
        <tr>
            <td style="padding:4px 0;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background:#faf9f5;border-radius:14px;overflow:hidden;">
                    @foreach([
                        ['🍅', 'Помодоро-таймер', 'Работайте в ритме 25/5 — концентрация без выгорания.'],
                        ['✅', 'Умный список задач', 'Сегодня, Входящие, Предстоящие — всё под рукой.'],
                        ['📅', 'Календарь', 'Планируйте события и держите дедлайны на виду.'],
                    ] as [$icon, $title, $desc])
                    <tr>
                        <td style="padding:16px 20px;border-bottom:1px solid #efeee7;" class="email-divider">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="width:36px;vertical-align:middle;font-size:20px;line-height:1;">{{ $icon }}</td>
                                    <td style="vertical-align:middle;padding-left:12px;">
                                        <p style="margin:0 0 2px;font-size:14px;font-weight:600;color:#1d1d1a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;" class="email-body-text">{{ $title }}</p>
                                        <p style="margin:0;font-size:13px;color:#6c6b62;line-height:1.5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;" class="email-muted">{{ $desc }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>

    {{-- CTA --}}
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
        <tr>
            <td align="center">
                <a href="{{ config('app.url') }}/app"
                   target="_blank"
                   style="display:inline-block;background:#e5533a;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;padding:14px 36px;border-radius:10px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;letter-spacing:-0.1px;">
                    Открыть Tomodoro →
                </a>
            </td>
        </tr>
    </table>

    {{-- Verify notice --}}
    @if(!$verified)
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="background:#fdf3f0;border-radius:10px;border-left:3px solid #e5533a;padding:14px 18px;">
                <p style="margin:0;font-size:13px;line-height:1.6;color:#6c6b62;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;" class="email-muted">
                    <strong style="color:#e5533a;">Подтвердите email</strong> — на этот адрес придёт письмо с кодом. Проверьте папку «Спам», если письмо не пришло.
                </p>
            </td>
        </tr>
    </table>
    @endif

</x-emails.layout>
