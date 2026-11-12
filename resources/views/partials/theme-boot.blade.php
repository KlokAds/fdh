{{-- Applies the theme before first paint to avoid a flash. "device" always follows the visitor's system setting (public site); otherwise a saved choice wins. Re-run by app.js after wire:navigate. --}}
<script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}" data-default-theme="{{ $defaultTheme }}">
    (function () {
        var fallback = document.currentScript.getAttribute('data-default-theme') || 'light';
        var saved = null;
        try { saved = localStorage.getItem('fdh-theme'); } catch (e) {}
        var theme = fallback === 'device' ? 'system' : (saved || fallback);
        if (theme === 'system') {
            theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-theme', theme === 'dark' ? 'dark' : 'light');
        document.documentElement.setAttribute('data-default-theme', fallback);
    })();
</script>
