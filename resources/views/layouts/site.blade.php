<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="light" @class(['lang-bn' => app()->getLocale() === 'bn'])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? site('meta_title') }}</title>
    @php($pageDescription = \Illuminate\Support\Str::limit(trim($description ?? '') !== '' ? $description : site('meta_description'), 160))
    @php($pageImage = $image ?? \App\Models\Setting::imageUrl('og_image', 'images/fdh-logo.jpg'))
    <meta name="description" content="{{ $pageDescription }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:site_name" content="{{ site('site_name') }}">
    <meta property="og:title" content="{{ $title ?? site('meta_title') }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $pageImage }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta property="og:locale" content="{{ app()->getLocale() === 'bn' ? 'bn_BD' : 'en_US' }}">
    <meta name="theme-color" content="#0a1628">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ $logoUrl }}">
    @include('partials.theme-boot', ['defaultTheme' => 'device'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;600;700&text=%E0%A7%A6%E0%A7%A7%E0%A7%A8%E0%A7%A9%E0%A7%AA%E0%A7%AB%E0%A7%AC%E0%A7%AD%E0%A7%AE%E0%A7%AF&display=swap" rel="stylesheet">
    @include('partials.brand-colors')
    @include('partials.meta-pixel')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="application/ld+json" nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'ProfessionalService',
            'name' => site('site_name'),
            'url' => url('/'),
            'logo' => $logoUrl,
            'telephone' => site('phone'),
            'email' => site('email'),
            'address' => ['@type' => 'PostalAddress', 'streetAddress' => site('rangpur_address'), 'addressLocality' => 'Rangpur', 'addressCountry' => 'BD'],
            'sameAs' => array_values(array_filter([site('facebook'), site('youtube'), site('linkedin')])),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}
    </script>
    @isset($schema)
        <script type="application/ld+json" nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @endisset
</head>
<body class="site">
    <a href="#main" class="skip-link">{{ __('Skip to content') }}</a>

    <div class="topbar">
        <div class="container topbar-inner">
            <span><x-icon name="pin" /> {{ __('Rangpur') }} · {{ __('Dhaka') }}</span>
            @if (site('office_hours'))<span class="topbar-hide-sm"><x-icon name="clock" /> {{ site('office_hours') }}</span>@endif
            <span class="topbar-end">
                <a href="mailto:{{ site('email') }}" class="topbar-hide-sm"><x-icon name="mail" /> {{ site('email') }}</a>
                @if (site('facebook'))<a href="{{ site('facebook') }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><x-icon name="facebook" /></a>@endif
                @if (site('youtube'))<a href="{{ site('youtube') }}" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><x-icon name="youtube" /></a>@endif
            </span>
        </div>
    </div>

    <header class="header" id="top" data-header>
        <div class="container nav">
            <a href="{{ route('home') }}" wire:navigate class="brand" aria-label="{{ site('site_name') }}">
                <img src="{{ $logoUrl }}" alt="" width="44" height="44" class="brand-logo">
                <span class="brand-text"><span class="brand-name">{{ site('site_name') }}</span><small class="brand-tag">{{ site('site_tagline') }}</small></span>
            </a>

            <nav class="menu" id="menu" aria-label="{{ __('Main menu') }}">
                <div class="menu-head">
                    <span>{{ __('Menu') }}</span>
                    <button type="button" class="icon-btn" data-menu-toggle aria-label="{{ __('Close menu') }}"><x-icon name="close" /></button>
                </div>
                <a href="{{ route('home') }}" wire:navigate wire:current.exact="is-active" class="menu-link"><x-icon name="home" class="menu-ic" />{{ __('Home') }}</a>
                <div class="has-dd" data-dropdown>
                    <a href="{{ route('services') }}" wire:navigate wire:current="is-active" class="menu-link"><x-icon name="layers" class="menu-ic" />{{ __('Services') }}</a>
                    <button type="button" class="dd-toggle" data-dropdown-toggle aria-expanded="false" aria-label="{{ __('Show services') }}"><x-icon name="chevron" /></button>
                    <div class="dd dd-wide">
                        <div class="dd-grid">
                            @foreach ($navServices as $navService)
                                <a href="{{ route('services.show', $navService) }}" wire:navigate class="dd-item" wire:key="nav-svc-{{ $navService->id }}">
                                    <span class="dd-ic"><x-icon :name="$navService->icon ?: 'building'" /></span>
                                    <span class="dd-text"><b>{{ $navService->title }}</b><small>{{ \Illuminate\Support\Str::limit($navService->summary, 64) }}</small></span>
                                </a>
                            @endforeach
                        </div>
                        <a href="{{ route('services') }}" wire:navigate class="dd-all">{{ __('All services') }} <x-icon name="arrow" /></a>
                    </div>
                </div>
                <a href="{{ route('projects') }}" wire:navigate wire:current="is-active" class="menu-link"><x-icon name="apartment" class="menu-ic" />{{ __('Projects') }}</a>
                @if ($calculatorOn)
                    <a href="{{ route('calculator') }}" wire:navigate wire:current="is-active" class="menu-link"><x-icon name="estimate" class="menu-ic" />{{ __('Cost calculator') }}</a>
                @endif
                <div class="has-dd" data-dropdown>
                    <a href="{{ route('about') }}" wire:navigate wire:current="is-active" class="menu-link"><x-icon name="users" class="menu-ic" />{{ __('About') }}</a>
                    <button type="button" class="dd-toggle" data-dropdown-toggle aria-expanded="false" aria-label="{{ __('More about us') }}"><x-icon name="chevron" /></button>
                    <div class="dd">
                        <a href="{{ route('about') }}" wire:navigate class="dd-item"><span class="dd-ic"><x-icon name="building" /></span><span class="dd-text"><b>{{ __('Who we are') }}</b><small>{{ __('Our story and principal engineer') }}</small></span></a>
                        <a href="{{ route('team') }}" wire:navigate class="dd-item"><span class="dd-ic"><x-icon name="users" /></span><span class="dd-text"><b>{{ __('Team') }}</b><small>{{ __('Engineers and architects behind your project') }}</small></span></a>
                        <a href="{{ route('faq') }}" wire:navigate class="dd-item"><span class="dd-ic"><x-icon name="help" /></span><span class="dd-text"><b>{{ __('FAQ') }}</b><small>{{ __('Approval, soil test, cost and timeline') }}</small></span></a>
                    </div>
                </div>
                <a href="{{ route('contact') }}" wire:navigate wire:current="is-active" class="menu-link"><x-icon name="mail" class="menu-ic" />{{ __('Contact') }}</a>

                <div class="menu-foot">
                    <a href="{{ site_phone_href() }}" class="btn"><x-icon name="phone" /> {{ site('phone') }}</a>
                    <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener noreferrer" class="btn btn-wa"><x-icon name="whatsapp" /> WhatsApp</a>
                    <p>{{ site('office_hours') }}</p>
                </div>
            </nav>

            <div class="nav-actions">
                <div class="lang-switch" role="group" aria-label="{{ __('Language') }}">
                    @foreach (config('fdh.locales') as $code => $meta)
                        <a href="{{ route('locale', $code) }}" @class(['is-active' => app()->getLocale() === $code]) lang="{{ $code }}" hreflang="{{ $code }}" title="{{ $meta['name'] }}">{{ $meta['short'] }}</a>
                    @endforeach
                </div>
                <a href="{{ site_phone_href() }}" class="nav-phone" aria-label="{{ __('Call us') }} {{ site('phone') }}">
                    <span class="nav-phone-icon"><x-icon name="phone" /></span>
                    <span class="nav-phone-text"><b>{{ site('phone') }}</b></span>
                </a>
                <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener noreferrer" class="nav-wa" aria-label="{{ __('Get free advice') }} — WhatsApp">
                    <x-icon name="whatsapp" /><span>{{ __('Get free advice') }}</span>
                </a>
                <button type="button" class="burger" data-menu-toggle aria-label="{{ __('Open menu') }}" aria-expanded="false" aria-controls="menu"><span></span><span></span></button>
            </div>
        </div>
    </header>
    <div class="menu-backdrop" data-menu-toggle></div>

    <main id="main">
        {{ $slot }}
    </main>

    <footer class="footer">
        <div class="container">
            <div class="foot-cta">
                <div>
                    <h2>{{ site('cta_title') }}</h2>
                    <p>{{ site('cta_sub') }}</p>
                </div>
                <div class="foot-cta-actions">
                    <a href="{{ site_phone_href() }}" class="btn btn-light"><x-icon name="phone" /> {{ site('phone') }}</a>
                    <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener noreferrer" class="btn btn-wa"><x-icon name="whatsapp" /> WhatsApp</a>
                </div>
            </div>
            <div class="foot">
                <div class="foot-brand">
                    <a href="{{ route('home') }}" wire:navigate class="foot-logo">
                        <img src="{{ $logoUrl }}" alt="" width="48" height="48">
                        <span>{{ site('site_name') }}<small>{{ site('site_tagline') }}</small></span>
                    </a>
                    <p>{{ site('meta_description') }}</p>
                    <div class="foot-social">
                        @if (site('facebook'))<a href="{{ site('facebook') }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><x-icon name="facebook" /></a>@endif
                        @if (site('youtube'))<a href="{{ site('youtube') }}" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><x-icon name="youtube" /></a>@endif
                        @if (site('linkedin'))<a href="{{ site('linkedin') }}" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn"><x-icon name="linkedin" /></a>@endif
                        <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"><x-icon name="whatsapp" /></a>
                    </div>
                </div>
                <nav class="foot-col" aria-label="{{ __('Quick links') }}">
                    <h4>{{ __('Quick links') }}</h4>
                    <a href="{{ route('home') }}" wire:navigate>{{ __('Home') }}</a>
                    <a href="{{ route('about') }}" wire:navigate>{{ __('About') }}</a>
                    <a href="{{ route('projects') }}" wire:navigate>{{ __('Projects') }}</a>
                    <a href="{{ route('faq') }}" wire:navigate>{{ __('Questions') }}</a>
                    <a href="{{ route('team') }}" wire:navigate>{{ __('Our Team') }}</a>
                    <a href="{{ route('contact') }}" wire:navigate>{{ __('Contact us') }}</a>
                </nav>
                <nav class="foot-col" aria-label="{{ __('Our services') }}">
                    <h4>{{ __('Our services') }}</h4>
                    @foreach ($navServices as $footService)
                        <a href="{{ route('services.show', $footService) }}" wire:navigate wire:key="foot-svc-{{ $footService->id }}">{{ $footService->title }}</a>
                    @endforeach
                </nav>
                <div class="foot-col foot-contact">
                    <h4>{{ __('Get in touch') }}</h4>
                    <a href="{{ site_phone_href() }}"><x-icon name="phone" /><span>{{ site('phone') }}</span></a>
                    <a href="mailto:{{ site('email') }}"><x-icon name="mail" /><span class="cc-break">{{ site('email') }}</span></a>
                    <p><x-icon name="pin" /><span><b>{{ __('Rangpur') }}</b> {{ site('rangpur_address') }}</span></p>
                    @if (site('dhaka_address'))
                        <p><x-icon name="pin" /><span><b>{{ __('Dhaka') }}</b> {{ site('dhaka_address') }}</span></p>
                    @endif
                    @if (site('office_hours'))
                        <p><x-icon name="clock" /><span>{{ site('office_hours') }}</span></p>
                    @endif
                </div>
            </div>
            <div class="foot-bottom">
                <span>© {{ date('Y') }} {{ site('site_name') }}. {{ __('All rights reserved.') }}</span>
                <a href="#top">{{ __('Back to top') }} ↑</a>
            </div>
        </div>
    </footer>

    @php($messengerPage = preg_match('#facebook\.com/([A-Za-z0-9.\-]+)/?$#', site('facebook'), $fbMatch) ? $fbMatch[1] : null)
    <nav class="mobile-bar" aria-label="{{ __('Quick contact') }}">
        <a href="{{ site_phone_href() }}"><x-icon name="phone" /><span>{{ __('Call') }}</span></a>
        <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener noreferrer" class="mb-wa"><x-icon name="whatsapp" /><span>WhatsApp</span></a>
        @if ($messengerPage)
            <a href="https://m.me/{{ $messengerPage }}" target="_blank" rel="noopener noreferrer" class="mb-ms"><x-icon name="chat" /><span>{{ __('Messenger') }}</span></a>
        @endif
        <a href="{{ route('contact') }}" wire:navigate class="mb-main"><x-icon name="mail" /><span>{{ __('Inquiry') }}</span></a>
    </nav>

    <a class="wa-float" href="https://wa.me/{{ site('whatsapp') }}?text={{ rawurlencode(__('Hello :site, I want to know about your services.', ['site' => site('site_name')])) }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Chat on WhatsApp') }}">
        <x-icon name="whatsapp" />
    </a>
</body>
</html>
