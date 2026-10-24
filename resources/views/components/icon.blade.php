@props(['name'])
@switch($name)
    @case('building')
        <svg viewBox="0 0 48 48" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M6 42h36M10 42V18l14-10 14 10v24M18 42V28h12v14M16 20h4M28 20h4"/></svg>
        @break
    @case('approval')
        <svg viewBox="0 0 48 48" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M12 6h18l8 8v28H12zM30 6v8h8M18 24h14M18 30h14M18 36h8"/><circle cx="34" cy="36" r="5"/><path d="m32 36 1.5 1.5L36.5 34"/></svg>
        @break
    @case('construction')
        <svg viewBox="0 0 48 48" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M8 40h32M14 40V22h20v18M10 22h28M24 8v14M24 8l12 8M24 8 12 16M20 30h8v10h-8z"/></svg>
        @break
    @case('survey')
        <svg viewBox="0 0 48 48" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M24 12l-10 30M24 12l10 30M17 32h14M6 42h36"/><circle cx="24" cy="10" r="3"/></svg>
        @break
    @case('estimate')
        <svg viewBox="0 0 48 48" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><rect x="10" y="6" width="28" height="36" rx="3"/><path d="M15 12h18v7H15zM16 25h3M23 25h3M30 25h3M16 31h3M23 31h3M30 31h3M16 37h3M23 37h10"/></svg>
        @break
    @case('interior')
        <svg viewBox="0 0 48 48" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M6 42h36M10 42V30a4 4 0 0 1 4-4h20a4 4 0 0 1 4 4v12M10 34h28M16 26v-6h16v6M24 6v8M20 14h8"/></svg>
        @break
    @case('home')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M3 11 12 4l9 7M5 9.5V20h14V9.5M10 20v-6h4v6"/></svg>
        @break
    @case('duplex')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M3 21h18M5 21V9l7-5 7 5v12M5 13h14M9 9h2M13 9h2M9 17h2M13 17h2"/></svg>
        @break
    @case('apartment')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M3 21h18M6 21V3h12v18M9 7h2M13 7h2M9 11h2M13 11h2M9 15h2M13 15h2M11 21v-3h2v3"/></svg>
        @break
    @case('shop')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M3 21h18M4 9l1.5-5h13L20 9M4 9h16a2.7 2.7 0 0 1-5.3 0 2.7 2.7 0 0 1-5.4 0A2.7 2.7 0 0 1 4 9zM5 11.5V21h14v-9.5M10 21v-5h4v5"/></svg>
        @break
    @case('renovation')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M3 21h18M5 21V11l6-4 6 4v3M9 21v-5h3M14.5 21l5-5a1.4 1.4 0 0 0-2-2l-5 5-.5 2.5z"/></svg>
        @break
    @case('align-left')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><rect x="3" y="5" width="8" height="7" rx="1"/><path d="M14 6h7M14 10h7M3 16h18M3 20h12"/></svg>
        @break
    @case('align-center')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><rect x="7" y="4" width="10" height="8" rx="1"/><path d="M3 16h18M6 20h12"/></svg>
        @break
    @case('align-right')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><rect x="13" y="5" width="8" height="7" rx="1"/><path d="M3 6h7M3 10h7M3 16h18M9 20h12"/></svg>
        @break
    @case('edit')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M4 20h4L19 9a2.8 2.8 0 0 0-4-4L4 16v4zM13.5 6.5l4 4"/></svg>
        @break
    @case('trash')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M4 7h16M10 11v6M14 11v6M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-12M9 7V4h6v3"/></svg>
        @break
    @case('key')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><circle cx="8" cy="15" r="4"/><path d="m11 12 9-9M17 6l3 3M15 8l2 2"/></svg>
        @break
    @case('power')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M12 3v9M6.3 6.3a8 8 0 1 0 11.4 0"/></svg>
        @break
    @case('alert')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M12 3 2 20h20L12 3zM12 10v4M12 17h.01"/></svg>
        @break
    @case('grid')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><rect x="3" y="3" width="7" height="8" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="15" width="7" height="6" rx="1.5"/></svg>
        @break
    @case('inbox')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M3 13h5l1.5 3h5L16 13h5M5.5 5h13L21 13v6a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-6z"/></svg>
        @break
    @case('image')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 16-5-5-9 9"/></svg>
        @break
    @case('users')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M18 14.5a6.5 6.5 0 0 1 3.5 5.5"/></svg>
        @break
    @case('list')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M9 6h12M9 12h12M9 18h12M4 6h.01M4 12h.01M4 18h.01"/></svg>
        @break
    @case('refresh')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M20 11a8 8 0 0 0-14.6-4.5L4 8M4 4v4h4M4 13a8 8 0 0 0 14.6 4.5L20 16M20 20v-4h-4"/></svg>
        @break
    @case('help')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6v.6M12 17h.01"/></svg>
        @break
    @case('upload')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M12 16V4M7 9l5-5 5 5M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/></svg>
        @break
    @case('bell')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M6 16v-5a6 6 0 0 1 12 0v5l1.5 2h-15zM10 20.5a2.2 2.2 0 0 0 4 0"/></svg>
        @break
    @case('user')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
        @break
    @case('settings')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M2 12h3M19 12h3M4.9 19.1 7 17M17 7l2.1-2.1"/></svg>
        @break
    @case('external')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>
        @break
    @case('logout')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3M10 17l-5-5 5-5M5 12h11"/></svg>
        @break
    @case('phone')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon-fill', 'aria-hidden' => 'true']) }}><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1z"/></svg>
        @break
    @case('mail')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
        @break
    @case('facebook')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon-fill', 'aria-hidden' => 'true']) }}><path d="M14 8h3V4h-3a4 4 0 0 0-4 4v2H8v4h2v6h4v-6h3l1-4h-4V8z"/></svg>
        @break
    @case('pin')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
        @break
    @case('shield')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M12 2 4 6v6c0 5 3.4 9.3 8 10 4.6-.7 8-5 8-10V6z"/><path d="m8.5 12 2.5 2.5 4.5-5"/></svg>
        @break
    @case('layers')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="m12 3 9 5-9 5-9-5zM3 13l9 5 9-5"/></svg>
        @break
    @case('chart')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M4 19h16M6 15l4-4 3 3 5-6"/></svg>
        @break
    @case('whatsapp')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon-fill', 'aria-hidden' => 'true']) }}><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.1-4.5-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.2.1.4 0 .5l-.4.6-.4.4c-.1.2-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.4 2.4 1.5.3.2.5.1.6-.1l.9-1c.2-.3.4-.2.6-.1l1.9.9c.3.1.5.2.5.3.1.2.1.7-.1 1.3z"/></svg>
        @break
    @case('clock')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        @break
    @case('youtube')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon-fill', 'aria-hidden' => 'true']) }}><path d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2 26 26 0 0 0 2 12a26 26 0 0 0 .4 4.8 2.5 2.5 0 0 0 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8A26 26 0 0 0 22 12a26 26 0 0 0-.4-4.8zM10 15V9l5.2 3z"/></svg>
        @break
    @case('linkedin')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon-fill', 'aria-hidden' => 'true']) }}><path d="M6.9 8.6H3.6V20h3.3zM5.3 3.5a1.9 1.9 0 1 0 0 3.8 1.9 1.9 0 0 0 0-3.8zM20.4 13.4c0-3.1-1.7-4.9-4.2-4.9a3.7 3.7 0 0 0-3.3 1.8V8.6H9.7V20H13v-5.7c0-1.5.3-2.9 2.1-2.9s1.9 1.7 1.9 3V20h3.3z"/></svg>
        @break
    @case('close')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M6 6l12 12M18 6 6 18"/></svg>
        @break
    @case('chevron')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="m6 9 6 6 6-6"/></svg>
        @break
    @case('moon')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M20 14.5A8.5 8.5 0 1 1 9.5 4a7 7 0 0 0 10.5 10.5z"/></svg>
        @break
    @case('sun')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
        @break
    @case('star')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon-fill', 'aria-hidden' => 'true']) }}><path d="m12 2.8 2.8 5.8 6.4.9-4.6 4.5 1.1 6.3L12 17.3l-5.7 3 1.1-6.3-4.6-4.5 6.4-.9z"/></svg>
        @break
    @case('quote')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon-fill', 'aria-hidden' => 'true']) }}><path d="M4 18v-5.5C4 8.4 6.1 6 9.8 5l.7 1.6C8.4 7.4 7.5 9 7.5 11H10v7zm10 0v-5.5c0-4.1 2.1-6.5 5.8-7.5l.7 1.6c-2.1.8-3 2.4-3 4.4H20v7z"/></svg>
        @break
    @case('plus')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M12 5v14M5 12h14"/></svg>
        @break
    @case('check')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>
        @break
    @case('chat')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-4.9A8 8 0 1 1 21 12z"/><path d="M8.5 11h.01M12 11h.01M15.5 11h.01"/></svg>
        @break
    @case('arrow')
        <svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true']) }}><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        @break
@endswitch
