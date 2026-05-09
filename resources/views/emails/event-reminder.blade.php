<x-emails.layout
    title="Напоминание о событии — Tomodoro"
    preheader="{{ $event['title'] }} — {{ $startsInText }}"
>
    {{-- ── Header ─────────────────────────────────────── --}}
    <x-slot:header>
        <p style="margin:0 0 10px;font-size:12px;font-weight:600;color:rgba(255,255,255,0.65);text-transform:uppercase;letter-spacing:1.2px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
            Напоминание о событии
        </p>
        <h1 style="margin:0;font-size:26px;font-weight:700;color:#ffffff;line-height:1.25;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
            📅 Скоро начнётся!
        </h1>
    </x-slot:header>

    {{-- ── Body ──────────────────────────────────────── --}}
    <p class="email-body-text" style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#1d1d1a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
        {{ $name }}, напоминаем о вашем событии:
    </p>

    {{-- Event card --}}
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
        <tr>
            <td style="border-radius:14px;overflow:hidden;border:1px solid #eeece4;" class="email-divider">

                {{-- Color stripe + title --}}
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td style="width:5px;background:{{ $event['color'] ?? '#e5533a' }};border-radius:14px 0 0 0;"></td>
                        <td style="padding:20px 20px 18px;background:#ffffff;" class="email-card">
                            <p style="margin:0 0 6px;font-size:18px;font-weight:700;color:#1d1d1a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;line-height:1.3;" class="email-body-text">
                                {{ $event['title'] }}
                            </p>
                            @if($event['description'])
                            <p style="margin:0;font-size:14px;color:#6c6b62;line-height:1.5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;" class="email-muted">
                                {{ $event['description'] }}
                            </p>
                            @endif
                        </td>
                    </tr>
                </table>

                {{-- Meta --}}
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td style="padding:14px 20px;background:#faf9f5;border-top:1px solid #eeece4;" class="email-divider">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    {{-- Date / Time --}}
                                    <td style="vertical-align:top;padding-right:24px;">
                                        <p style="margin:0 0 2px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.7px;color:#9a988d;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;" class="email-muted">
                                            Дата и время
                                        </p>
                                        <p style="margin:0;font-size:14px;font-weight:600;color:#1d1d1a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;" class="email-body-text">
                                            @if($event['all_day'])
                                                {{ \Carbon\Carbon::parse($event['starts_at'])->isoFormat('D MMMM, dddd') }}<br>
                                                <span style="font-weight:400;color:#6c6b62;">Весь день</span>
                                            @else
                                                {{ \Carbon\Carbon::parse($event['starts_at'])->isoFormat('D MMMM, dddd') }}<br>
                                                {{ \Carbon\Carbon::parse($event['starts_at'])->format('H:i') }}
                                                @if($event['ends_at'])
                                                — {{ \Carbon\Carbon::parse($event['ends_at'])->format('H:i') }}
                                                @endif
                                            @endif
                                        </p>
                                    </td>
                                    {{-- Starts in --}}
                                    <td style="vertical-align:top;">
                                        <p style="margin:0 0 2px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.7px;color:#9a988d;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;" class="email-muted">
                                            До начала
                                        </p>
                                        <p style="margin:0;font-size:14px;font-weight:600;color:#e5533a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
                                            {{ $startsInText }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

    {{-- CTA --}}
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center">
                <a href="{{ config('app.url') }}/app/calendar"
                   target="_blank"
                   style="display:inline-block;background:#e5533a;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;padding:14px 36px;border-radius:10px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;letter-spacing:-0.1px;">
                    Открыть календарь →
                </a>
            </td>
        </tr>
    </table>

</x-emails.layout>
