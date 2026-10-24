<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New inquiry</title>
</head>
<body style="margin:0;padding:0;background:#eef2f7;font-family:Arial,Helvetica,sans-serif;color:#14213a;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f7;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:14px;overflow:hidden;">
                <tr>
                    <td style="background:#0a1628;padding:22px 26px;color:#ffffff;">
                        <div style="font-size:12px;letter-spacing:2px;text-transform:uppercase;color:#7cc8f2;">{{ $siteName }}</div>
                        <div style="font-size:21px;font-weight:bold;margin-top:6px;">New website inquiry</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:24px 26px 6px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:15px;line-height:1.5;">
                            <tr><td style="color:#5f6b7d;width:120px;padding:5px 0;">Name</td><td style="font-weight:bold;padding:5px 0;">{{ $inquiry->name }}</td></tr>
                            <tr><td style="color:#5f6b7d;padding:5px 0;">Phone</td><td style="font-weight:bold;padding:5px 0;"><a href="{{ $telUrl }}" style="color:#1596dc;">{{ $inquiry->phone }}</a></td></tr>
                            @if ($inquiry->email)
                                <tr><td style="color:#5f6b7d;padding:5px 0;">Email</td><td style="padding:5px 0;"><a href="mailto:{{ $inquiry->email }}" style="color:#1596dc;">{{ $inquiry->email }}</a></td></tr>
                            @endif
                            <tr><td style="color:#5f6b7d;padding:5px 0;">Service</td><td style="padding:5px 0;">{{ $inquiry->service ?: '—' }}</td></tr>
                            <tr><td style="color:#5f6b7d;padding:5px 0;">Location</td><td style="padding:5px 0;">{{ $inquiry->location ?: '—' }}</td></tr>
                            <tr><td style="color:#5f6b7d;padding:5px 0;">Language</td><td style="padding:5px 0;">{{ $inquiry->locale === 'bn' ? 'বাংলা' : 'English' }}</td></tr>
                            <tr><td style="color:#5f6b7d;padding:5px 0;">Received</td><td style="padding:5px 0;">{{ $inquiry->created_at->timezone('Asia/Dhaka')->format('d M Y, h:i A') }}</td></tr>
                        </table>
                        @if ($inquiry->message)
                            <div style="margin-top:14px;padding:14px;background:#f6f8fb;border:1px solid #dfe5ee;border-radius:10px;font-size:14px;line-height:1.6;white-space:pre-wrap;">{{ $inquiry->message }}</div>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="padding:20px 26px 26px;">
                        <a href="{{ $openUrl }}" style="display:inline-block;background:#1596dc;color:#ffffff;text-decoration:none;font-weight:bold;padding:12px 20px;border-radius:9px;margin:0 8px 8px 0;">Open in admin &amp; mark read</a>
                        <a href="{{ $whatsappUrl }}" style="display:inline-block;background:#25d366;color:#ffffff;text-decoration:none;font-weight:bold;padding:12px 20px;border-radius:9px;margin:0 8px 8px 0;">WhatsApp</a>
                        <a href="{{ $telUrl }}" style="display:inline-block;background:#ffffff;color:#14213a;text-decoration:none;font-weight:bold;padding:11px 19px;border-radius:9px;border:1px solid #dfe5ee;margin:0 0 8px 0;">Call</a>
                    </td>
                </tr>
                <tr>
                    <td style="padding:14px 26px;background:#f6f8fb;color:#8692a6;font-size:12px;">
                        You receive this because your address is set under Admin → Content &amp; settings → Contact &amp; offices.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<img src="{{ $pixelUrl }}" width="1" height="1" alt="" style="display:block;width:1px;height:1px;border:0;">
</body>
</html>
