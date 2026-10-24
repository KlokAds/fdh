<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0a1628">
    <link rel="manifest" href="{{ route('admin.manifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-192.png') }}">
    <title>{{ $title ?? 'Admin' }} — {{ \App\Models\Setting::read('site_name', 'en') }} Admin</title>
    <link rel="icon" href="{{ \App\Models\Setting::imageUrl('logo', 'images/fdh-logo.jpg') }}">
    @include('partials.theme-boot', ['defaultTheme' => 'light'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Manrope:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600&display=swap" rel="stylesheet">
    @include('partials.brand-colors')
    @vite(['resources/css/admin.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="side-brand">
            <img src="{{ \App\Models\Setting::imageUrl('logo', 'images/fdh-logo.jpg') }}" alt="" width="40" height="40">
            <div><strong>FDH Admin</strong><small>{{ \App\Models\Setting::read('site_tagline', 'en') }}</small></div>
        </a>
        <nav class="side-nav" aria-label="Admin">
            <a href="{{ route('admin.dashboard') }}" wire:navigate wire:current.exact="is-active">Dashboard</a>
            @can('inquiries.view')
                <a href="{{ route('admin.inquiries') }}" wire:navigate wire:current="is-active">Inquiries</a>
                <a href="{{ route('admin.notifications') }}" wire:navigate wire:current="is-active">Notifications</a>
            @endcan

            @canany(['content.manage', 'services.manage', 'projects.manage', 'team.manage', 'faqs.manage', 'testimonials.manage', 'settings.manage'])
                <span class="side-sep">Website</span>
            @endcanany
            @canany(['content.manage', 'settings.manage'])<a href="{{ route('admin.content') }}" wire:navigate wire:current="is-active">Content &amp; settings</a>@endcanany
            @can('services.manage')<a href="{{ route('admin.services') }}" wire:navigate wire:current="is-active">Services</a>@endcan
            @can('projects.manage')<a href="{{ route('admin.projects') }}" wire:navigate wire:current="is-active">Projects</a>@endcan
            @can('team.manage')<a href="{{ route('admin.team') }}" wire:navigate wire:current="is-active">Team</a>@endcan
            @can('faqs.manage')<a href="{{ route('admin.faqs') }}" wire:navigate wire:current="is-active">FAQs</a>@endcan
            @can('testimonials.manage')<a href="{{ route('admin.testimonials') }}" wire:navigate wire:current="is-active">Testimonials</a>@endcan

            <span class="side-sep">Security</span>
            @can('users.manage')
                <a href="{{ route('admin.users') }}" wire:navigate wire:current="is-active">Users</a>
                <a href="{{ route('admin.roles') }}" wire:navigate wire:current="is-active">Roles &amp; permissions</a>
            @endcan
            <a href="{{ route('admin.security') }}" wire:navigate wire:current="is-active">My account security</a>
            @can('logs.view')<a href="{{ route('admin.activity') }}" wire:navigate wire:current="is-active">Security log</a>@endcan
        </nav>
        <div class="side-foot">
            <a href="{{ route('home') }}" target="_blank" rel="noopener">View website ↗</a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="link-btn">Sign out</button>
            </form>
        </div>
    </aside>
    <div class="sidebar-backdrop" data-sidebar-toggle></div>

    <div class="admin-main">
        <header class="topbar">
            <button type="button" class="burger" data-sidebar-toggle aria-label="Toggle menu" aria-controls="sidebar"><span></span><span></span><span></span></button>
            <h1>{{ $title ?? 'Admin' }}</h1>
            <button type="button" class="icon-btn" data-theme-toggle aria-label="Toggle dark mode" title="Toggle dark mode">
                <x-icon name="moon" class="theme-moon" /><x-icon name="sun" class="theme-sun" />
            </button>
            <span class="who">{{ auth()->user()->name }}</span>
        </header>
        <div class="admin-content">
            @if (session('status'))
                <p class="alert alert-success" role="status">{{ session('status') }}</p>
            @endif
            {{ $slot }}
        </div>
    </div>
</body>
</html>
