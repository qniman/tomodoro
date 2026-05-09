<x-emails.layout
    title="Сброс пароля — Tomodoro"
    preheader="Вы запросили сброс пароля. Ссылка действительна 60 минут."
>
    {{-- ── Header ─────────────────────────────────────── --}}
    <x-slot:header>
        <p style="margin:0 0 10px;font-size:12px;font-weight:600;color:rgba(255,255,255,0.65);text-transform:uppercase;letter-spacing:1.2px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
            Безопасность
        </p>
        <h1 style="margin:0;font-size:26px;font-weight:700;color:#ffffff;line-height:1.25;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
            Сброс пароля 🔑
        </h1>
    </x-slot:header>

    {{-- ── Body ──────────────────────────────────────── --}}
    <p class="email-body-text" style="margin:0 0 8px;font-size:15px;line-height:1.7;color:#1d1d1a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
        {{ $name }}, мы получили запрос на сброс пароля для аккаунта <strong>{{ $email }}</strong>.
    </p>
    <p class="email-body-text" style="margin:0 0 32px;font-size:15px;line-height:1.7;color:#1d1d1a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
        Нажмите кнопку ниже, чтобы создать новый пароль:
    </p>

    {{-- CTA --}}
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
        <tr>
            <td align="center">
                <a href="{{ $resetUrl }}"
                   target="_blank"
                   style="display:inline-block;background:#e5533a;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;padding:14px 36px;border-radius:10px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;letter-spacing:-0.1px;">
                    Создать новый пароль →
                </a>
            </td>
        </tr>
    </table>

    {{-- Expiry notice --}}
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
        <tr>
            <td align="center">
                <p style="margin:0;font-size:13px;color:#9a988d;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;" class="email-muted">
                    ⏱ Ссылка действительна <strong style="color:#6c6b62;">60 минут</strong>
                </p>
            </td>
        </tr>
    </table>

    {{-- Raw link fallback --}}
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
        <tr>
            <td style="background:#f5f4f0;border-radius:10px;padding:14px 18px;">
                <p style="margin:0 0 4px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:#9a988d;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;" class="email-muted">
                    Кнопка не работает? Перейдите по ссылке:
                </p>
                <p style="margin:0;font-size:12px;color:#6c6b62;word-break:break-all;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;" class="email-muted">
                    <a href="{{ $resetUrl }}" style="color:#e5533a;text-decoration:none;" target="_blank">{{ $resetUrl }}</a>
                </p>
            </td>
        </tr>
    </table>

    {{-- Security notice --}}
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="border-top:1px solid #eeece4;padding-top:24px;" class="email-divider">
                <p style="margin:0;font-size:12px;line-height:1.7;color:#9a988d;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;" class="email-muted">
                    🛡 Если вы не запрашивали сброс пароля — просто проигнорируйте это письмо. Ваш пароль останется прежним.
                </p>
            </td>
        </tr>
    </table>

</x-emails.layout>
