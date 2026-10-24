@php
    $accent = preg_match('/^#[0-9a-fA-F]{6}$/', site('accent_color')) ? site('accent_color') : '#1596dc';
    $brandDark = preg_match('/^#[0-9a-fA-F]{6}$/', site('brand_dark')) ? site('brand_dark') : '#0a1628';
@endphp
<style nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
    :root {
        --accent: {{ $accent }};
        --accent-2: color-mix(in srgb, {{ $accent }} 55%, #ffffff);
        --accent-3: color-mix(in srgb, {{ $accent }} 70%, #000000);
        --brand-dark: {{ $brandDark }};
        --brand-dark-2: color-mix(in srgb, {{ $brandDark }} 88%, #ffffff);
    }
</style>
