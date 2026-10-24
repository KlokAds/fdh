<!DOCTYPE html>
<html lang="en" data-default-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Admin' }} — {{ \App\Models\Setting::read('site_name', 'en') }}</title>
    <link rel="icon" href="{{ \App\Models\Setting::imageUrl('logo', 'images/fdh-logo.jpg') }}">
    @include('partials.theme-boot', ['defaultTheme' => 'light'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    @include('partials.brand-colors')
    @vite(['resources/css/admin.css', 'resources/js/app.js'])
</head>
<body class="auth-body">
    <main class="auth-wrap">
        <div class="auth-card">
            <div class="auth-brand">
                <img src="{{ \App\Models\Setting::imageUrl('logo', 'images/fdh-logo.jpg') }}" alt="" width="56" height="56">
                <div><strong>{{ \App\Models\Setting::read('site_name', 'en') }}</strong><small>Admin panel</small></div>
            </div>
            @if (session('status'))
                <p class="alert alert-info" role="status">{{ session('status') }}</p>
            @endif
            {{ $slot }}
        </div>
        <p class="auth-foot">🔒 Protected area. All sign-in activity is logged.</p>
    </main>
</body>
</html>
