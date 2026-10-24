@php($pixelId = \App\Support\MetaConversions::pixelId())
@if ($pixelId !== '')
    {{-- Meta Pixel: loads once; app.js tracks PageView on wire:navigate and Lead after an inquiry. --}}
    <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}" data-navigate-once data-pixel-id="{{ $pixelId }}">
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', document.currentScript.getAttribute('data-pixel-id'));
        fbq('track', 'PageView');
    </script>
@endif
