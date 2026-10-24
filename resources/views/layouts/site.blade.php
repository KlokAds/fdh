<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="light" @class(['lang-bn' => app()->getLocale() === 'bn'])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? site('meta_title') }}</title>
    <meta name="description" content="{{ site('meta_description') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ site('site_name') }}">
    <meta property="og:title" content="{{ $title ?? site('meta_title') }}">
    <meta property="og:description" content="{{ site('meta_description') }}">
    <meta property="og:image" content="{{ \App\Models\Setting::imageUrl('og_image', 'images/fdh-logo.jpg') }}">
    <meta property="og:locale" content="{{ app()->getLocale() === 'bn' ? 'bn_BD' : 'en_US' }}">
    <meta name="theme-color" content="#0a1628">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ $logoUrl }}">
    @include('partials.theme-boot', ['defaultTheme' => site('default_theme')])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600&family=Inter:wght@400;500;600&family=Hind+Siliguri:wght@400;500;600&display=swap" rel="stylesheet">
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
</head>
<body class="site">
    <a href="#main" class="skip-link">{{ __('Skip to content') }}</a>

    <header class="header" id="top" data-header>
        <div class="container nav">
            <a href="{{ route('home') }}" wire:navigate class="brand" aria-label="{{ site('site_name') }}">
                <img src="{{ $logoUrl }}" alt="" width="36" height="36" class="brand-logo">
                <span class="brand-name">{{ site('site_name') }}</span>
            </a>

            <nav class="menu" id="menu" aria-label="{{ __('Main menu') }}">
                <div class="menu-head">
                    <span>{{ __('Menu') }}</span>
                    <button type="button" class="icon-btn" data-menu-toggle aria-label="{{ __('Close menu') }}"><x-icon name="close" /></button>
                </div>
                <a href="{{ route('about') }}" wire:navigate wire:current="is-active" class="menu-link">{{ __('About') }}</a>
                <div class="has-dd" data-dropdown>
                    <a href="{{ route('services') }}" wire:navigate wire:current="is-active" class="menu-link">{{ __('Services') }}</a>
                    <button type="button" class="dd-toggle" data-dropdown-toggle aria-expanded="false" aria-label="{{ __('Show services') }}"><x-icon name="chevron" /></button>
                    <div class="dd">
                        @foreach ($navServices as $navService)
                            <a href="{{ route('services.show', $navService) }}" wire:navigate class="dd-item" wire:key="nav-svc-{{ $navService->id }}">
                                <span class="dd-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span>{{ $navService->title }}</span>
                            </a>
                        @endforeach
                        <a href="{{ route('services') }}" wire:navigate class="dd-all">{{ __('All services') }} →</a>
                    </div>
                </div>
                <a href="{{ route('projects') }}" wire:navigate wire:current="is-active" class="menu-link">{{ __('Projects') }}</a>
                @if ($calculatorOn)
                    <a href="{{ route('calculator') }}" wire:navigate wire:current="is-active" class="menu-link">{{ __('Cost calculator') }}</a>
                @endif
                <a href="{{ route('team') }}" wire:navigate wire:current="is-active" class="menu-link">{{ __('Team') }}</a>
                <a href="{{ route('faq') }}" wire:navigate wire:current="is-active" class="menu-link">{{ __('FAQ') }}</a>
                <a href="{{ route('contact') }}" wire:navigate wire:current="is-active" class="menu-link menu-link-mobile">{{ __('Contact') }}</a>

                <div class="menu-foot">
                    <a href="{{ site_phone_href() }}">{{ site('phone') }}</a>
                    <a href="mailto:{{ site('email') }}">{{ site('email') }}</a>
                </div>
            </nav>

            <div class="nav-actions">
                <div class="lang-switch" role="group" aria-label="{{ __('Language') }}">
                    @foreach (config('fdh.locales') as $code => $meta)
                        <a href="{{ route('locale', $code) }}" @class(['is-active' => app()->getLocale() === $code]) lang="{{ $code }}" hreflang="{{ $code }}" title="{{ $meta['name'] }}">{{ $meta['short'] }}</a>
                    @endforeach
                </div>
                <button type="button" class="icon-btn" data-theme-toggle aria-label="{{ __('Toggle dark mode') }}" title="{{ __('Toggle dark mode') }}">
                    <x-icon name="moon" class="theme-moon" /><x-icon name="sun" class="theme-sun" />
                </button>
                <a href="{{ route('contact') }}" wire:navigate class="btn btn-sm nav-cta">{{ __('Contact') }}</a>
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
            <div class="foot-top">
                <p class="foot-wordmark">{{ site('site_name') }}</p>
                <p class="foot-tag">{{ site('footer_tagline') }}</p>
            </div>
            <div class="foot">
                <div class="foot-col">
                    <h4>{{ __('Rangpur') }}</h4>
                    <p>{{ site('rangpur_address') }}</p>
                </div>
                @if (site('dhaka_address'))
                    <div class="foot-col">
                        <h4>{{ __('Dhaka') }}</h4>
                        <p>{{ site('dhaka_address') }}</p>
                    </div>
                @endif
                <div class="foot-col">
                    <h4>{{ __('Contact') }}</h4>
                    <a href="{{ site_phone_href() }}">{{ site('phone') }}</a>
                    <a href="mailto:{{ site('email') }}">{{ site('email') }}</a>
                    @if (site('office_hours'))<p>{{ site('office_hours') }}</p>@endif
                </div>
                <div class="foot-col">
                    <h4>{{ __('Follow') }}</h4>
                    @if (site('facebook'))<a href="{{ site('facebook') }}" target="_blank" rel="noopener noreferrer">Facebook ↗</a>@endif
                    @if (site('youtube'))<a href="{{ site('youtube') }}" target="_blank" rel="noopener noreferrer">YouTube ↗</a>@endif
                    @if (site('linkedin'))<a href="{{ site('linkedin') }}" target="_blank" rel="noopener noreferrer">LinkedIn ↗</a>@endif
                    <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener noreferrer">WhatsApp ↗</a>
                </div>
            </div>
            <div class="foot-bottom">
                <span>© {{ date('Y') }} {{ site('site_name') }} · {{ site('site_tagline') }}</span>
                <nav aria-label="{{ __('Footer') }}">
                    <a href="{{ route('services') }}" wire:navigate>{{ __('Services') }}</a>
                    <a href="{{ route('projects') }}" wire:navigate>{{ __('Projects') }}</a>
                    <a href="{{ route('about') }}" wire:navigate>{{ __('About') }}</a>
                    @if ($calculatorOn)<a href="{{ route('calculator') }}" wire:navigate>{{ __('Cost calculator') }}</a>@endif
                    <a href="#top">{{ __('Back to top') }} ↑</a>
                </nav>
            </div>
        </div>
    </footer>

    <a class="wa-float" href="https://wa.me/{{ site('whatsapp') }}?text={{ rawurlencode(__('Hello :site, I want to know about your services.', ['site' => site('site_name')])) }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Chat on WhatsApp') }}">
        <x-icon name="whatsapp" />
    </a>
</body>
</html>
