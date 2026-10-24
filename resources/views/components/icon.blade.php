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
