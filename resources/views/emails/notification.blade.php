<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>BetterGov Notification</title>
<!--[if mso]>
<style type="text/css">
body, table, td, p, a { font-family: Arial, Helvetica, sans-serif !important; }
</style>
<![endif]-->
<style type="text/css">
    /* Reset */
    body, table, td, p, a, span { margin: 0; padding: 0; }
    body { background-color: #f1f5f9; font-family: 'Segoe UI', Helvetica, Arial, sans-serif; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
    table { border-collapse: collapse; }
    img { border: 0; display: block; }

    /* Outer wrapper */
    .wrapper { background-color: #f1f5f9; width: 100%; }
    .wrapper-td { padding: 40px 16px; }

    /* Card */
    .card { width: 600px; max-width: 600px; background-color: #ffffff; }

    /* Header */
    .header-td { background-color: #0f172a; padding: 28px 40px; }
    .header-brand { color: #ffffff; font-size: 20px; font-weight: 700; display: block; }
    .header-sub { color: #94a3b8; font-size: 12px; display: block; margin-top: 4px; }

    /* Body */
    .body-td { padding: 36px 40px 28px; }

    /* Type pill — on a span so border-radius actually renders */
    .pill { font-size: 12px; font-weight: 600; padding: 5px 14px; border-radius: 20px; display: inline-block; }

    /* Greeting */
    .greeting { font-size: 22px; font-weight: 700; color: #0f172a; line-height: 1.3; margin: 0 0 10px; }

    /* Message */
    .message { font-size: 15px; color: #475569; line-height: 1.7; margin: 0 0 32px; }

    /* CTA button — border-radius on the <a>, VML used for Outlook (see template) */
    .btn-td { border-radius: 6px; }
    .btn-a { display: inline-block; padding: 13px 28px; color: #ffffff; font-size: 14px; font-weight: 600; text-decoration: none; border-radius: 6px; }

    /* Reference */
    .reference { font-size: 12px; color: #94a3b8; margin: 0; }

    /* Divider */
    .divider-td { padding: 0 40px; }
    .divider-inner { border-top: 1px solid #e2e8f0; font-size: 0; line-height: 0; mso-line-height-rule: exactly; }

    /* Footer */
    .footer-td { padding: 24px 40px 36px; }
    .footer-text { font-size: 12px; color: #94a3b8; line-height: 1.6; margin: 0 0 6px; }
    .footer-copy { font-size: 12px; color: #cbd5e1; margin: 0; }
</style>
</head>
<body>

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td class="wrapper-td" align="center">

            <!-- Card: width attribute for Outlook, max-width via CSS for others -->
            <table class="card" width="600" style="width:600px;max-width:600px;background-color:#ffffff;" cellpadding="0" cellspacing="0" border="0">

                <!-- Header -->
                <tr>
                    <td class="header-td">
                        <span class="header-brand">BetterGov</span>
                        <span class="header-sub">Government Services Portal</span>
                    </td>
                </tr>

                <!-- Colour accent bar -->
                <tr>
                    <td height="4" bgcolor="{{ $barColor }}" style="font-size:0;line-height:0;mso-line-height-rule:exactly;">&nbsp;</td>
                </tr>

                <!-- Body -->
                <tr>
                    <td class="body-td">

                        <!-- Type pill -->
                        <p style="margin:0 0 24px;">
                            <table cellpadding="0" cellspacing="0" border="0" style="display:inline-table;">
                                <tr>
                                    <td bgcolor="{{ $pillBg }}" style="border-radius:20px;padding:5px 14px;">
                                        <font color="{{ $barColor }}" class="pill" style="display:inline-block;">
                                            {{ $typeIcon }} {{ $typeLabel }}
                                        </font>
                                    </td>
                                </tr>
                            </table>
                        </p>

                        <!-- Greeting -->
                        <p class="greeting">Hello, {{ $recipientName }}</p>

                        <!-- Message -->
                        <p class="message">{{ $notifMessage }}</p>

                        <!-- CTA button -->
                        @if ($actionUrl)
                        <table cellpadding="0" cellspacing="0" border="0" style="margin-bottom:36px;">
                            <tr>
                                <td class="btn-td" bgcolor="{{ $barColor }}">
                                    <!--[if mso]>
                                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml"
                                        xmlns:w="urn:schemas-microsoft-com:office:word"
                                        href="{{ $actionUrl }}"
                                        style="height:44px;v-text-anchor:middle;width:200px;"
                                        arcsize="14%" stroke="f" fillcolor="{{ $barColor }}">
                                        <w:anchorlock/>
                                        <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:14px;font-weight:bold;">
                                            {{ $actionLabel }}
                                        </center>
                                    </v:roundrect>
                                    <![endif]-->
                                    <!--[if !mso]><!-->
                                    <a href="{{ $actionUrl }}" class="btn-a" style="color:#ffffff;">
                                        {{ $actionLabel }} &rarr;
                                    </a>
                                    <!--<![endif]-->
                                </td>
                            </tr>
                        </table>
                        @endif

                        <!-- Request reference -->
                        @if ($requestId)
                        <p class="reference">Reference: Request #{{ $requestId }}</p>
                        @endif

                    </td>
                </tr>

                <!-- Divider -->
                <tr>
                    <td class="divider-td">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td class="divider-inner">&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td class="footer-td">
                        <p class="footer-text">
                            You are receiving this because you have an account on BetterGov.
                            You can turn off email notifications in your account settings.
                        </p>
                        <p class="footer-copy">
                            &copy; {{ date('Y') }} BetterGov &mdash; Government Services Portal
                        </p>
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>