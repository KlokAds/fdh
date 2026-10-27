{{--
    Branded email frame (table layout and inline styles, so it renders the same in Gmail, Outlook and phone mail apps).
--}}
@props(['title', 'kicker' => null, 'preheader' => '', 'lang' => 'en'])
@php
    $siteName = \App\Models\Setting::read('site_name', $lang);
    $logo = \App\Models\Setting::imageUrl('logo', 'images/fdh-logo.jpg');
    $navy = '#0a1628';
    $amber = '#f2a900';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:0;background:#eeeae1;font-family:'Hind Siliguri','Noto Sans Bengali',Arial,Helvetica,sans-serif;color:#1b2430;-webkit-text-size-adjust:100%;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ $preheader }}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eeeae1;padding:28px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border:1px solid #d9d3c4;">
                <tr>
                    <td style="background:{{ $navy }};padding:20px 28px;border-bottom:4px solid {{ $amber }};">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="52" style="vertical-align:middle;"><img src="{{ $logo }}" width="44" height="44" alt="" style="display:block;background:#ffffff;border:0;"></td>
                                <td style="vertical-align:middle;padding-left:12px;">
                                    <div style="color:#ffffff;font-size:17px;font-weight:bold;line-height:1.2;">{{ $siteName }}</div>
                                    <div style="color:{{ $amber }};font-size:11px;letter-spacing:2px;text-transform:uppercase;font-weight:bold;margin-top:3px;">{{ \App\Models\Setting::read('site_tagline', 'en') }}</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:30px 28px 8px;">
                        @if ($kicker)
                            <div style="font-size:12px;letter-spacing:2px;text-transform:uppercase;font-weight:bold;color:#0b5d93;margin-bottom:8px;"><span style="display:inline-block;width:22px;height:3px;background:{{ $amber }};vertical-align:middle;margin-right:8px;"></span>{{ $kicker }}</div>
                        @endif
                        <h1 style="margin:0;font-size:24px;line-height:1.3;color:#1b2430;">{{ $title }}</h1>
                    </td>
                </tr>
                {{ $slot }}
                <tr>
                    <td style="padding:22px 28px;background:#f6f3ec;border-top:1px solid #e5dfd2;font-size:13px;line-height:1.6;color:#5b6270;">
                        <b style="color:#1b2430;">{{ $siteName }}</b><br>
                        {{ \App\Models\Setting::read('rangpur_address', $lang) }}
                        @if (\App\Models\Setting::read('dhaka_address', $lang))<br>{{ \App\Models\Setting::read('dhaka_address', $lang) }}@endif
                        <br><a href="{{ site_phone_href() }}" style="color:#0b5d93;text-decoration:none;">{{ \App\Models\Setting::read('phone', $lang) }}</a>
                        &nbsp;·&nbsp;<a href="{{ url('/') }}" style="color:#0b5d93;text-decoration:none;">{{ parse_url(url('/'), PHP_URL_HOST) }}</a>
                    </td>
                </tr>
            </table>
            @isset($footnote)
                <div style="max-width:600px;margin-top:14px;font-size:12px;line-height:1.5;color:#8a8f99;">{{ $footnote }}</div>
            @endisset
        </td>
    </tr>
</table>
{{ $after ?? '' }}
</body>
</html>
