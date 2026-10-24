<!DOCTYPE html>
<html lang="en" data-default-theme="light">
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
    @php
        $me = auth()->user();
        $route = request()->route()?->getName() ?? '';
        $websiteRoutes = ['admin.content', 'admin.services', 'admin.projects', 'admin.team', 'admin.faqs', 'admin.testimonials', 'admin.media'];
        $adminRoutes = ['admin.notifications', 'admin.users', 'admin.roles', 'admin.activity', 'admin.system-update', 'admin.security'];
        $section = in_array($route, $websiteRoutes, true) ? 'Website' : (in_array($route, $adminRoutes, true) ? 'Administration' : null);
        $canWebsite = $me->canAny(['content.manage', 'services.manage', 'projects.manage', 'team.manage', 'faqs.manage', 'testimonials.manage', 'media.manage', 'settings.manage']);
    @endphp

    {{-- Command bar: brand, grouped navigation, quick tools --}}
    <header class="cmd">
        <div class="cmd-inner">
            <button type="button" class="cmd-burger" data-sidebar-toggle aria-label="Open menu" aria-controls="sidebar"><span></span><span></span><span></span></button>
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="cmd-brand">
                <img src="{{ \App\Models\Setting::imageUrl('logo', 'images/fdh-logo.jpg') }}" alt="" width="38" height="38">
                <span><b>{{ \App\Models\Setting::read('site_name', 'en') }}</b><small>Site office</small></span>
            </a>

            <nav class="cmd-nav" id="sidebar" aria-label="Admin">
                <div class="cmd-nav-head">
                    <b>Menu</b>
                    <button type="button" class="cmd-close" data-sidebar-toggle aria-label="Close menu"><x-icon name="close" /></button>
                </div>
                <a href="{{ route('admin.dashboard') }}" wire:navigate wire:current.exact="is-active" class="cmd-link"><x-icon name="grid" /> Dashboard</a>
                @can('inquiries.view')
                    <a href="{{ route('admin.inquiries') }}" wire:navigate wire:current="is-active" class="cmd-link"><x-icon name="inbox" /> Inquiries</a>
                @endcan
                @if ($canWebsite)
                    <div @class(['cmd-group', 'is-current' => $section === 'Website']) data-dropdown>
                        <button type="button" class="cmd-link" data-dropdown-toggle aria-expanded="false"><x-icon name="layers" /> Website <x-icon name="chevron" class="cmd-caret" /></button>
                        <div class="cmd-menu">
                            @canany(['content.manage', 'settings.manage'])<a href="{{ route('admin.content') }}" wire:navigate wire:current="is-active"><x-icon name="settings" /><span><b>Content &amp; settings</b><small>Texts, logo, contact, SEO</small></span></a>@endcanany
                            @can('services.manage')<a href="{{ route('admin.services') }}" wire:navigate wire:current="is-active"><x-icon name="layers" /><span><b>Services</b><small>What the firm offers</small></span></a>@endcan
                            @can('projects.manage')<a href="{{ route('admin.projects') }}" wire:navigate wire:current="is-active"><x-icon name="apartment" /><span><b>Projects</b><small>Portfolio and photos</small></span></a>@endcan
                            @can('team.manage')<a href="{{ route('admin.team') }}" wire:navigate wire:current="is-active"><x-icon name="user" /><span><b>Team</b><small>Engineers and staff</small></span></a>@endcan
                            @can('faqs.manage')<a href="{{ route('admin.faqs') }}" wire:navigate wire:current="is-active"><x-icon name="help" /><span><b>FAQs</b><small>Questions and answers</small></span></a>@endcan
                            @can('testimonials.manage')<a href="{{ route('admin.testimonials') }}" wire:navigate wire:current="is-active"><x-icon name="chat" /><span><b>Testimonials</b><small>Client reviews</small></span></a>@endcan
                            @can('media.manage')<a href="{{ route('admin.media') }}" wire:navigate wire:current="is-active"><x-icon name="image" /><span><b>Media library</b><small>Every photo, used or not</small></span></a>@endcan
                        </div>
                    </div>
                @endif
                <div @class(['cmd-group', 'is-current' => $section === 'Administration']) data-dropdown>
                    <button type="button" class="cmd-link" data-dropdown-toggle aria-expanded="false"><x-icon name="shield" /> Administration <x-icon name="chevron" class="cmd-caret" /></button>
                    <div class="cmd-menu">
                        @can('inquiries.view')<a href="{{ route('admin.notifications') }}" wire:navigate wire:current="is-active"><x-icon name="bell" /><span><b>Notifications</b><small>Phone alerts for inquiries</small></span></a>@endcan
                        @can('users.manage')
                            <a href="{{ route('admin.users') }}" wire:navigate wire:current="is-active"><x-icon name="users" /><span><b>Users</b><small>Who can sign in</small></span></a>
                            <a href="{{ route('admin.roles') }}" wire:navigate wire:current="is-active"><x-icon name="key" /><span><b>Roles &amp; permissions</b><small>What each role may do</small></span></a>
                        @endcan
                        @can('logs.view')<a href="{{ route('admin.activity') }}" wire:navigate wire:current="is-active"><x-icon name="list" /><span><b>Security log</b><small>Sign-ins and changes</small></span></a>@endcan
                        @can('system.update')<a href="{{ route('admin.system-update') }}" wire:navigate wire:current="is-active"><x-icon name="refresh" /><span><b>System update</b><small>Install the newest version</small></span></a>@endcan
                        <a href="{{ route('admin.security') }}" wire:navigate wire:current="is-active"><x-icon name="shield" /><span><b>My account security</b><small>Password and two-factor</small></span></a>
                    </div>
                </div>
                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="cmd-link cmd-site"><x-icon name="external" /> View website</a>
            </nav>
            <div class="sidebar-backdrop" data-sidebar-toggle></div>

            <div class="cmd-tools">
                <button type="button" class="cmd-tool" data-theme-toggle aria-label="Toggle dark mode" title="Toggle dark mode">
                    <x-icon name="moon" class="theme-moon" /><x-icon name="sun" class="theme-sun" />
                </button>
                <livewire:admin.topbar-alerts />
                <div class="tb-dd tb-profile" data-dropdown>
                    <button type="button" class="tb-user" data-dropdown-toggle aria-expanded="false" aria-label="Account menu">
                        <span class="tb-avatar">{{ mb_strtoupper(mb_substr($me->name, 0, 1)) }}</span>
                        <span class="tb-user-text"><b>{{ $me->name }}</b><small>{{ $me->roles->pluck('name')->first() }}</small></span>
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
                            <a href="{{ route('home') }}" target="_blank" rel="noopener"><x-icon name="external" /> View website</a>
                        </nav>
                        <form method="POST" action="{{ route('admin.logout') }}" class="tb-logout">
                            @csrf
                            <button type="submit"><x-icon name="logout" /> Sign out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Title block, like the corner of a drawing sheet --}}
    <div class="sheet-head">
        <div class="sheet-head-inner">
            <nav class="sheet-crumbs" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" wire:navigate>Site office</a>
                @if ($section)<span>/</span><span>{{ $section }}</span>@endif
                <span>/</span><span>{{ $title ?? 'Admin' }}</span>
            </nav>
            <h1>{{ $title ?? 'Admin' }}</h1>
        </div>
        <span class="sheet-stamp" aria-hidden="true">{{ now()->timezone(config('fdh.display_timezone'))->format('d M Y') }}</span>
    </div>

    <main class="admin-content">
        {{ $slot }}
    </main>

    {{-- Toasts: success and error messages from every action --}}
    <div class="toasts" id="toasts" aria-live="polite" aria-atomic="false"></div>
    @if (session('status'))
        <template data-initial-toast data-type="success">{{ session('status') }}</template>
    @endif

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
</body>
</html>
