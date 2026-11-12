<x-mail.frame :title="__('Thank you, :name — we have your request', ['name' => $inquiry->name])" :kicker="__('Request received')" :preheader="__('An engineer will call you soon. Your request number is :number.', ['number' => $number])" :lang="$lang">
    <tr>
        <td style="padding:16px 28px 4px;font-size:15px;line-height:1.75;color:#3a4553;">
            <p style="margin:0 0 14px;">{{ __('We have received your request. An engineer from our team will call you on :phone, usually within one working day.', ['phone' => $inquiry->phone]) }}</p>
            <p style="margin:0;">{{ __('If it is urgent, call or message us directly — the buttons are below.') }}</p>
        </td>
    </tr>
    <tr>
        <td style="padding:18px 28px 4px;">
            <div style="font-size:12px;letter-spacing:2px;text-transform:uppercase;font-weight:bold;color:#6b7180;margin-bottom:6px;">{{ __('What you sent us') }}</div>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:15px;line-height:1.5;border-top:2px solid #1b2430;">
                @foreach (array_filter([
                    __('Request no.') => $number,
                    __('Service') => $inquiry->service ?: __('General inquiry'),
                    __('Project location') => $inquiry->location,
                    __('Mobile number') => $inquiry->phone,
                ]) as $label => $value)
                    <tr>
                        <td style="width:150px;padding:10px 0;border-bottom:1px solid #e5dfd2;color:#6b7180;font-size:14px;">{{ $label }}</td>
                        <td style="padding:10px 0;border-bottom:1px solid #e5dfd2;font-weight:bold;">{{ $value }}</td>
                    </tr>
                @endforeach
            </table>
            @if ($inquiry->message)
                <div style="margin-top:16px;padding:14px 16px;background:#f6f3ec;border-left:4px solid #f2a900;font-size:14px;line-height:1.65;white-space:pre-wrap;">{{ $inquiry->message }}</div>
            @endif
        </td>
    </tr>
    <tr>
        <td style="padding:22px 28px 6px;">
            <div style="font-size:12px;letter-spacing:2px;text-transform:uppercase;font-weight:bold;color:#6b7180;margin-bottom:10px;">{{ __('What happens next') }}</div>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;line-height:1.55;color:#3a4553;">
                @foreach ([__('We call you to understand the land, budget and timeline.'), __('If needed, we visit the site and plan the survey and soil test.'), __('You get a clear quotation before any work starts.')] as $step)
                    <tr>
                        <td style="width:34px;vertical-align:top;padding:6px 0;"><span style="display:inline-block;width:24px;height:24px;line-height:24px;text-align:center;background:#0a1628;color:#f2a900;font-weight:bold;font-size:13px;">{{ $loop->iteration }}</span></td>
                        <td style="padding:8px 0;">{{ $step }}</td>
                    </tr>
                @endforeach
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding:22px 28px 32px;">
            <a href="{{ site_phone_href() }}" style="display:inline-block;background:#0a1628;color:#ffffff;text-decoration:none;font-weight:bold;padding:13px 22px;margin:0 8px 8px 0;">{{ __('Call') }} {{ \App\Models\Setting::read('phone', $lang) }}</a>
            <a href="https://wa.me/{{ \App\Models\Setting::read('whatsapp', $lang) }}" style="display:inline-block;background:#1fae54;color:#ffffff;text-decoration:none;font-weight:bold;padding:13px 22px;margin:0 0 8px 0;">WhatsApp</a>
        </td>
    </tr>
    <x-slot:footnote>{{ __('You get this email because this address was entered in the contact form on our website. Just reply to this email if you want to add anything.') }}</x-slot:footnote>
</x-mail.frame>
