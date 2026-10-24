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
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;600;700&text=%E0%A7%A6%E0%A7%A7%E0%A7%A8%E0%A7%A9%E0%A7%AA%E0%A7%AB%E0%A7%AC%E0%A7%AD%E0%A7%AE%E0%A7%AF&display=swap" rel="stylesheet">
    @include('partials.brand-colors')
    @vite(['resources/css/admin.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="side-brand">
            <img src="{{ \App\Models\Setting::imageUrl('logo', 'images/fdh-logo.jpg') }}" alt="" width="40" height="40">
            <div><strong>{{ \App\Models\Setting::read('site_name', 'en') }}</strong><small>Admin panel</small></div>
        </a>
        <nav class="side-nav" aria-label="Admin">
            <a href="{{ route('admin.dashboard') }}" wire:navigate wire:current.exact="is-active"><x-icon name="grid" /> Dashboard</a>
            @can('inquiries.view')
                <a href="{{ route('admin.inquiries') }}" wire:navigate wire:current="is-active"><x-icon name="inbox" /> Inquiries</a>
            @endcan

            @canany(['content.manage', 'services.manage', 'projects.manage', 'team.manage', 'faqs.manage', 'testimonials.manage', 'media.manage', 'settings.manage'])
                <span class="side-sep">Website</span>
            @endcanany
            @canany(['content.manage', 'settings.manage'])<a href="{{ route('admin.content') }}" wire:navigate wire:current="is-active"><x-icon name="settings" /> Content &amp; settings</a>@endcanany
            @can('services.manage')<a href="{{ route('admin.services') }}" wire:navigate wire:current="is-active"><x-icon name="layers" /> Services</a>@endcan
            @can('projects.manage')<a href="{{ route('admin.projects') }}" wire:navigate wire:current="is-active"><x-icon name="apartment" /> Projects</a>@endcan
            @can('team.manage')<a href="{{ route('admin.team') }}" wire:navigate wire:current="is-active"><x-icon name="user" /> Team</a>@endcan
            @can('faqs.manage')<a href="{{ route('admin.faqs') }}" wire:navigate wire:current="is-active"><x-icon name="help" /> FAQs</a>@endcan
            @can('testimonials.manage')<a href="{{ route('admin.testimonials') }}" wire:navigate wire:current="is-active"><x-icon name="chat" /> Testimonials</a>@endcan
            @can('media.manage')<a href="{{ route('admin.media') }}" wire:navigate wire:current="is-active"><x-icon name="image" /> Media library</a>@endcan

            <span class="side-sep">Administration</span>
            @can('inquiries.view')<a href="{{ route('admin.notifications') }}" wire:navigate wire:current="is-active"><x-icon name="bell" /> Notifications</a>@endcan
            @can('users.manage')
                <a href="{{ route('admin.users') }}" wire:navigate wire:current="is-active"><x-icon name="users" /> Users</a>
                <a href="{{ route('admin.roles') }}" wire:navigate wire:current="is-active"><x-icon name="shield" /> Roles &amp; permissions</a>
            @endcan
            @can('logs.view')<a href="{{ route('admin.activity') }}" wire:navigate wire:current="is-active"><x-icon name="list" /> Security log</a>@endcan
            @can('system.update')<a href="{{ route('admin.system-update') }}" wire:navigate wire:current="is-active"><x-icon name="refresh" /> System update</a>@endcan
        </nav>
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="side-site"><x-icon name="external" /> View website</a>
    </aside>
    <div class="sidebar-backdrop" data-sidebar-toggle></div>

    {{-- Styled replacement for the browser's confirm() box; driven by data-confirm in app.js. --}}
    <div class="modal" id="confirm-modal" role="alertdialog" aria-modal="true" aria-labelledby="confirm-title" aria-describedby="confirm-text" hidden>
        <div class="modal-backdrop" data-confirm-cancel></div>
        <div class="modal-card">
            <span class="modal-icon"><x-icon name="alert" /></span>
            <h2 id="confirm-title">Are you sure?</h2>
            <p id="confirm-text"></p>
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" data-confirm-cancel>Cancel</button>
                <button type="button" class="btn btn-danger-solid" data-confirm-ok>Yes, continue</button>
            </div>
        </div>
    </div>

    <div class="admin-main">
        <header class="topbar">
            <button type="button" class="burger" data-sidebar-toggle aria-label="Toggle menu" aria-controls="sidebar"><span></span><span></span><span></span></button>
            <h1>{{ $title ?? 'Admin' }}</h1>
            <button type="button" class="icon-btn" data-theme-toggle aria-label="Toggle dark mode" title="Toggle dark mode">
                <x-icon name="moon" class="theme-moon" /><x-icon name="sun" class="theme-sun" />
            </button>
            <livewire:admin.topbar-alerts />
            @php($me = auth()->user())
            <div class="tb-dd tb-profile" data-dropdown>
                <button type="button" class="tb-user" data-dropdown-toggle aria-expanded="false" aria-label="Account menu">
                    <span class="tb-avatar">{{ mb_strtoupper(mb_substr($me->name, 0, 1)) }}</span>
                    <span class="tb-user-text"><b>{{ $me->name }}</b><small>{{ $me->roles->pluck('name')->first() }}</small></span>
                    <x-icon name="chevron" class="tb-chevron" />
                </button>
                <div class="tb-panel">
                    <div class="tb-panel-head tb-me">
                        <span class="tb-avatar tb-avatar-lg">{{ mb_strtoupper(mb_substr($me->name, 0, 1)) }}</span>
                        <span><b>{{ $me->name }}</b><small>{{ $me->email }}</small></span>
                    </div>
                    <nav class="tb-links" aria-label="Account">
                        <a href="{{ route('admin.security') }}" wire:navigate><x-icon name="shield" /> My account &amp; security</a>
                        @canany(['content.manage', 'settings.manage'])
                            <a href="{{ route('admin.content') }}" wire:navigate><x-icon name="settings" /> Website settings</a>
                        @endcanany
                        @can('inquiries.view')
                            <a href="{{ route('admin.notifications') }}" wire:navigate><x-icon name="bell" /> Notification settings</a>
                        @endcan
                        @can('users.manage')
                            <a href="{{ route('admin.users') }}" wire:navigate><x-icon name="user" /> Users &amp; roles</a>
                        @endcan
                        <a href="{{ route('home') }}" target="_blank" rel="noopener"><x-icon name="external" /> View website</a>
                    </nav>
                    <form method="POST" action="{{ route('admin.logout') }}" class="tb-logout">
                        @csrf
                        <button type="submit"><x-icon name="logout" /> Sign out</button>
                    </form>
                </div>
            </div>
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
