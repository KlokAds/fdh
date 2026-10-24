{{-- Applies the saved/default theme before first paint to avoid a flash. Re-run by app.js after wire:navigate. --}}
<script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}" data-default-theme="{{ $defaultTheme }}">
    (function () {
        var fallback = document.currentScript.getAttribute('data-default-theme') || 'light';
        var saved = null;
        try { saved = localStorage.getItem('fdh-theme'); } catch (e) {}
        var theme = saved || fallback;
        if (theme === 'system') {
            theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-theme', theme === 'dark' ? 'dark' : 'light');
        document.documentElement.setAttribute('data-default-theme', fallback);
    })();
</script>
