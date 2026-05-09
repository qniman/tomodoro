<x-emails.layout
    title="Подтвердите email — Tomodoro"
    preheader="Ваш код подтверждения: {{ $code }}. Действителен 30 минут."
>
    {{-- ── Header ─────────────────────────────────────── --}}
    <x-slot:header>
        <p style="margin:0 0 10px;font-size:12px;font-weight:600;color:rgba(255,255,255,0.65);text-transform:uppercase;letter-spacing:1.2px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
            Подтверждение email
        </p>
        <h1 style="margin:0;font-size:26px;font-weight:700;color:#ffffff;line-height:1.25;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
            Введите код 🔐
        </h1>
    </x-slot:header>

    {{-- ── Body ──────────────────────────────────────── --}}
    <p class="email-body-text" style="margin:0 0 28px;font-size:15px;line-height:1.7;color:#1d1d1a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
        {{ $name }}, вот ваш код для подтверждения адреса <strong>{{ $email }}</strong>:
    </p>

    {{-- OTP code boxes --}}
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:10px;">
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        @php $digits = str_split($code); @endphp
                        @foreach($digits as $i => $digit)
                        {{-- Gap after 3rd digit --}}
                        @if($i === 3)
                        <td style="width:16px;"></td>
                        @endif
                        <td style="padding:0 4px;">
                            <div style="
                                width:52px;
                                height:64px;
                                background:#f5f4f0;
                                border:2px solid #e8e6dd;
                                border-radius:12px;
                                display:table-cell;
                                vertical-align:middle;
                                text-align:center;
                                font-size:32px;
                                font-weight:700;
                                color:#1d1d1a;
                                letter-spacing:-1px;
                                font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;
                                line-height:64px;
                            ">{{ $digit }}</div>
                        </td>
                        @endforeach
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 32px;font-size:13px;line-height:1.6;color:#9a988d;text-align:center;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;" class="email-muted">
        Код действителен&nbsp;<strong style="color:#6c6b62;">30 минут</strong>
    </p>

    {{-- CTA --}}
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:32px;">
        <tr>
            <td align="center">
                <a href="{{ config('app.url') }}/verify-email"
                   target="_blank"
                   style="display:inline-block;background:#e5533a;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;padding:14px 36px;border-radius:10px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;letter-spacing:-0.1px;">
                    Ввести код →
                </a>
            </td>
        </tr>
    </table>

    {{-- Security notice --}}
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="border-top:1px solid #eeecE4;padding-top:24px;" class="email-divider">
                <p style="margin:0;font-size:12px;line-height:1.7;color:#9a988d;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;" class="email-muted">
                    🛡 Если вы не регистрировались в Tomodoro — просто проигнорируйте это письмо. Никто не получит доступ к вашему аккаунту.
                </p>
            </td>
        </tr>
    </table>

</x-emails.layout>
