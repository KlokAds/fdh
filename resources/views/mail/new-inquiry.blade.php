<x-mail.frame :title="'New inquiry from '.$inquiry->name" kicker="Website inquiry" :preheader="($inquiry->service ?: 'General inquiry').' · '.$inquiry->phone">
    <tr>
        <td style="padding:18px 28px 6px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:15px;line-height:1.5;border-top:2px solid #1b2430;">
                @foreach (array_filter([
                    'Name' => e($inquiry->name),
                    'Phone' => '<a href="'.e($telUrl).'" style="color:#0b5d93;font-weight:bold;text-decoration:none;">'.e($inquiry->phone).'</a>',
                    'Email' => $inquiry->email ? '<a href="mailto:'.e($inquiry->email).'" style="color:#0b5d93;text-decoration:none;">'.e($inquiry->email).'</a>' : null,
                    'Service' => e($inquiry->service ?: '—'),
                    'Location' => e($inquiry->location ?: '—'),
                    'Language' => $inquiry->locale === 'bn' ? 'বাংলা' : 'English',
                    'Received' => e($inquiry->created_at->timezone(config('fdh.display_timezone'))->format('d M Y, h:i A')),
                ]) as $label => $value)
                    <tr>
                        <td style="width:120px;padding:10px 0;border-bottom:1px solid #e5dfd2;color:#6b7180;font-size:13px;text-transform:uppercase;letter-spacing:1px;">{{ $label }}</td>
                        <td style="padding:10px 0;border-bottom:1px solid #e5dfd2;font-weight:bold;">{!! $value !!}</td>
                    </tr>
                @endforeach
            </table>
            @if ($inquiry->message)
                <div style="margin-top:18px;padding:14px 16px;background:#f6f3ec;border-left:4px solid #f2a900;font-size:14px;line-height:1.65;white-space:pre-wrap;">{{ $inquiry->message }}</div>
            @endif
        </td>
    </tr>
    <tr>
        <td style="padding:22px 28px 30px;">
            <a href="{{ $openUrl }}" style="display:inline-block;background:#f2a900;color:#1b1300;text-decoration:none;font-weight:bold;padding:13px 22px;margin:0 8px 8px 0;">Open in admin &amp; mark read</a>
            <a href="{{ $whatsappUrl }}" style="display:inline-block;background:#1fae54;color:#ffffff;text-decoration:none;font-weight:bold;padding:13px 22px;margin:0 8px 8px 0;">WhatsApp</a>
            <a href="{{ $telUrl }}" style="display:inline-block;background:#0a1628;color:#ffffff;text-decoration:none;font-weight:bold;padding:13px 22px;margin:0 0 8px 0;">Call</a>
        </td>
    </tr>
    <x-slot:footnote>You receive this because your address is set under Admin → Content &amp; settings → Contact &amp; offices. Replying to this email answers the visitor directly when they left an email address.</x-slot:footnote>
    <x-slot:after><img src="{{ $pixelUrl }}" width="1" height="1" alt="" style="display:block;width:1px;height:1px;border:0;"></x-slot:after>
</x-mail.frame>
