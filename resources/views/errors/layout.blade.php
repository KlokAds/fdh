{{-- Branded error page. Uses no database calls so it still renders when the site itself is failing. --}}
<!DOCTYPE html>
<html lang="bn" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('code') · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('images/fdh-logo.jpg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="site error-page">
    <main class="error-wrap">
        <a href="{{ url('/') }}" class="error-brand"><img src="{{ asset('images/fdh-logo.jpg') }}" alt="" width="56" height="56"><span>{{ config('app.name') }}</span></a>
        <p class="error-code">@yield('code')</p>
        <h1>@yield('title_bn')</h1>
        <p class="error-text">@yield('text_bn')</p>
        <p class="error-text error-en">@yield('title_en') — @yield('text_en')</p>
        <div class="error-actions">
            <a href="{{ url('/') }}" class="btn">হোমপেজে যান</a>
            <a href="{{ url('/contact') }}" class="btn btn-outline">যোগাযোগ করুন</a>
        </div>
    </main>
</body>
</html>
