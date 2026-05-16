<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>BetterGov Appointment</title>
<!--[if mso]>
<style type="text/css">
body, table, td, p, a { font-family: Arial, Helvetica, sans-serif !important; }
</style>
<![endif]-->
<style type="text/css">
    body, table, td, p, a, span { margin: 0; padding: 0; }
    body { background-color: #f1f5f9; font-family: 'Segoe UI', Helvetica, Arial, sans-serif; -webkit-text-size-adjust: 100%; }
    table { border-collapse: collapse; }

    .wrapper    { background-color: #f1f5f9; width: 100%; }
    .wrapper-td { padding: 40px 16px; }
    .card       { width: 600px; max-width: 600px; background-color: #ffffff; }

    .header-td  { background-color: #0f172a; padding: 28px 40px; }
    .header-brand { color: #ffffff; font-size: 20px; font-weight: 700; display: block; }
    .header-sub   { color: #94a3b8; font-size: 12px; display: block; margin-top: 4px; }

    .body-td { padding: 36px 40px 28px; }
    .pill { font-size: 12px; font-weight: 600; padding: 5px 14px; border-radius: 20px; display: inline-block; }
    .greeting { font-size: 22px; font-weight: 700; color: #0f172a; line-height: 1.3; margin: 0 0 10px; }
    .lead      { font-size: 15px; color: #475569; line-height: 1.7; margin: 0 0 24px; }

    .detail-table { width: 100%; border-collapse: collapse; margin-bottom: 28px; }
    .detail-table td { padding: 10px 14px; font-size: 14px; border-bottom: 1px solid #f1f5f9; }
    .detail-label  { color: #64748b; font-weight: 600; width: 40%; }
    .detail-value  { color: #1e293b; }

    .btn-td { border-radius: 6px; }
    .btn-a  { display: inline-block; padding: 13px 28px; color: #ffffff; font-size: 14px; font-weight: 600; text-decoration: none; border-radius: 6px; }

    .divider-td    { padding: 0 40px; }
    .divider-inner { border-top: 1px solid #e2e8f0; font-size: 0; line-height: 0; mso-line-height-rule: exactly; }
    .footer-td     { padding: 24px 40px 36px; }
    .footer-text   { font-size: 12px; color: #94a3b8; line-height: 1.6; margin: 0 0 6px; }
    .footer-copy   { font-size: 12px; color: #cbd5e1; margin: 0; }
</style>
</head>
<body>

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td class="wrapper-td" align="center">

            <table class="card" width="600" cellpadding="0" cellspacing="0" border="0"
                   style="width:600px;max-width:600px;background-color:#ffffff;">

                <!-- Header -->
                <tr>
                    <td class="header-td">
                        <span class="header-brand">BetterGov</span>
                        <span class="header-sub">Government Services Portal</span>
                    </td>
                </tr>

                <!-- Accent bar -->
                <tr>
                    <td height="4" >&nbsp;</td>
                </tr>

                <!-- Body -->
                <tr>
                    <td class="body-td">

                        <!-- Type pill -->
                        <p style="margin:0 0 24px;">
                            <span class="pill" >
                                {{ $typeIcon }} {{ $typeLabel }}
                            </span>
                        </p>

                        <!-- Greeting -->
                        <p class="greeting">Hello, {{ $recipientName }}</p>

                        <!-- Lead text -->
                        @if ($type === 'reminder')
                        <p class="lead">This is a friendly reminder that you have an appointment <strong>tomorrow</strong>. Please arrive on time and bring any relevant documents.</p>
                        @elseif ($type === 'confirmation')
                        <p class="lead">Your appointment has been <strong>confirmed</strong> by the office. We look forward to seeing you.</p>
                        @else
                        <p class="lead">Your appointment has been <strong>cancelled</strong>. You can book a new appointment at any time through the BetterGov portal.</p>
                        @endif

                        <!-- Appointment details -->
                        @if ($type !== 'cancelled')
                        <table class="detail-table">
                            <tr>
                                <td class="detail-label">Office</td>
                                <td class="detail-value">{{ $officeName }}</td>
                            </tr>
                            @if ($officeAddress)
                            <tr>
                                <td class="detail-label">Address</td>
                                <td class="detail-value">{{ $officeAddress }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="detail-label">Date</td>
                                <td class="detail-value">{{ $appointmentDate }}</td>
                            </tr>
                            <tr>
                                <td class="detail-label">Time</td>
                                <td class="detail-value">{{ $appointmentTime }}</td>
                            </tr>
                            @if ($notes)
                            <tr>
                                <td class="detail-label">Notes</td>
                                <td class="detail-value">{{ $notes }}</td>
                            </tr>
                            @endif
                        </table>
                        @endif

                        <!-- CTA button -->
                        <table cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
                            <tr>
                                <td class="btn-td" >
                                    <!--[if mso]>
                                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml"
                                        xmlns:w="urn:schemas-microsoft-com:office:word"
                                        href="{{ $actionUrl }}"
                                        style="height:44px;v-text-anchor:middle;width:220px;"
                                        arcsize="14%" stroke="f" fillcolor="{{ $barColor }}">
                                        <w:anchorlock/>
                                        <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:14px;font-weight:bold;">
                                            {{ $actionLabel }}
                                        </center>
                                    </v:roundrect>
                                    <![endif]-->
                                    <!--[if !mso]><!-->
                                    <a href="{{ $actionUrl }}" class="btn-a"
                                       >
                                        {{ $actionLabel }} &rarr;
                                    </a>
                                    <!--<![endif]-->
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                <!-- Divider -->
                <tr>
                    <td class="divider-td">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr><td class="divider-inner">&nbsp;</td></tr>
                        </table>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td class="footer-td">
                        <p class="footer-text">
                            You are receiving this because you have an appointment booked on BetterGov.
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