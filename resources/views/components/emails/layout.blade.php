@props([
    'title'     => 'Tomodoro',
    'preheader' => '',
])

<!DOCTYPE html>
<html lang="ru" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $title }}</title>
    <!--[if mso]>
    <noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
    <![endif]-->
    <style>
        /* ── Reset ───────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }
        body  { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        table { border-collapse: collapse !important; mso-table-lspace: 0; mso-table-rspace: 0; }
        img   { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
        a     { color: #e5533a; }

        /* ── Base ───────────────────────────────────── */
        body {
            background-color: #f5f4f0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
        }

        /* ── Dark mode ──────────────────────────────── */
        @media (prefers-color-scheme: dark) {
            body, .email-bg  { background-color: #19181a !important; }
            .email-card      { background-color: #242322 !important; }
            .email-body-text { color: #eceae6 !important; }
            .email-muted     { color: #a09e95 !important; }
            .email-divider   { border-color: #2f2e2c !important; }
            .email-tag       { background-color: #2f2e2c !important; color: #a09e95 !important; }
            .email-footer-text { color: #706e65 !important; }
        }

        /* ── Responsive ─────────────────────────────── */
        @media only screen and (max-width: 640px) {
            .email-outer-wrap { padding: 20px 12px !important; }
            .email-card-body  { padding: 32px 24px !important; }
            .email-header     { padding: 28px 24px 24px !important; }
            .email-logo-wrap  { padding-bottom: 16px !important; }
        }
    </style>
</head>
<body class="email-bg" style="margin:0;padding:0;background-color:#f5f4f0;">

    {{-- Preheader: hidden text shown in inbox preview --}}
    @if($preheader)
        <div aria-hidden="true" style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#f5f4f0;white-space:nowrap;">
            {{ $preheader }}&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
        </div>
    @endif

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td class="email-outer-wrap" align="center" style="padding: 48px 16px 56px;">

                {{-- ── Logo ──────────────────────────────────────── --}}
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px;">
                    <tr>
                        <td class="email-logo-wrap" align="center" style="padding-bottom:20px;">
                            <a href="{{ config('app.url') }}" style="text-decoration:none;display:inline-block;" target="_blank">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="vertical-align:middle;padding-right:8px;font-size:24px;line-height:1;">
                                            🍅
                                        </td>
                                        <td style="vertical-align:middle;">
                                            <span style="font-size:19px;font-weight:700;color:#1d1d1a;letter-spacing:-0.4px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">Tomodoro</span>
                                        </td>
                                    </tr>
                                </table>
                            </a>
                        </td>
                    </tr>
                </table>

                {{-- ── Card ──────────────────────────────────────── --}}
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="email-card" style="max-width:600px;background-color:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 4px 32px rgba(20,19,14,0.09),0 1px 4px rgba(20,19,14,0.05);">

                    {{-- Header stripe --}}
                    <tr>
                        <td class="email-header" style="background:linear-gradient(145deg,#e5533a 0%,#c4412b 100%);padding:36px 48px 32px;">
                            {{ $header }}
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td class="email-card-body" style="padding:40px 48px;">
                            {{ $slot }}
                        </td>
                    </tr>

                </table>

                {{-- ── Footer ─────────────────────────────────────── --}}
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px;margin-top:28px;">
                    <tr>
                        <td align="center" style="padding:0 20px;">
                            <p class="email-footer-text" style="margin:0 0 5px;font-size:12px;line-height:1.7;color:#9a988d;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
                                Это письмо отправлено автоматически — пожалуйста, не отвечайте на него.
                            </p>
                            <p class="email-footer-text" style="margin:0;font-size:12px;line-height:1.7;color:#b5b2a8;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
                                © {{ date('Y') }} Tomodoro &nbsp;·&nbsp;
                                <a href="{{ config('app.url') }}" style="color:#b5b2a8;text-decoration:none;" target="_blank">tomodoro.online</a>
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>
