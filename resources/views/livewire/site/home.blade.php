<div>
    {{-- HERO: site photo under a navy wash, headline on the left, the logo skyline on the right --}}
    <section class="hero">
        @if (\App\Models\Setting::raw()['hero_image'] ?? '')
            <img class="hero-photo" src="{{ \App\Models\Setting::imageUrl('hero_image', 'images/fdh-logo.jpg') }}" alt="" fetchpriority="high">
        @endif
        <div class="hero-shade" aria-hidden="true"></div>
        <div class="container hero-inner">
            <div class="hero-copy">
                <p class="kicker reveal">{{ site('hero_eyebrow') }}</p>
                <h1 class="display reveal">{{ site('hero_title_start') }} <em>{{ site('hero_title_highlight') }}</em> {{ site('hero_title_end') }}</h1>
                <p class="lead reveal">{{ site('hero_lead') }}</p>
                <div class="hero-actions reveal">
                    <a href="{{ route('contact') }}" wire:navigate class="btn btn-amber">{{ site('hero_cta') }} <x-icon name="arrow" /></a>
                    <a href="{{ site_phone_href() }}" class="btn btn-ghost-light"><x-icon name="phone" /> {{ site('phone') }}</a>
                </div>
                <ul class="hero-trust reveal">
                    <li><x-icon name="check" /> {{ __('BNBC-compliant design') }}</li>
                    <li><x-icon name="check" /> {{ __('RAJUK / City Corp approval') }}</li>
                    <li><x-icon name="check" /> {{ __('Engineer on site') }}</li>
                </ul>
            </div>

            {{-- Animated skyline drawn after the FDH logo: towers rise in on page load. --}}
            <div class="hero-art" aria-hidden="true">
                <svg viewBox="0 0 420 420" class="skyline">
                    <defs>
                        <linearGradient id="gSilver" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0" stop-color="#ffffff"/><stop offset=".5" stop-color="#c4c9d1"/><stop offset="1" stop-color="#6f7887"/>
                        </linearGradient>
                        <linearGradient id="gBlue" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0" stop-color="#3fa2ff"/><stop offset="1" stop-color="#0b4f9c"/>
                        </linearGradient>
                    </defs>
                    <circle cx="210" cy="210" r="196" class="ring"/>
                    <circle cx="210" cy="210" r="160" class="ring ring2"/>
                    <g class="plan"><path d="M40 300h120v80H40zM160 300h70v80h-70zM40 340h60M100 300v40M230 330h40v50"/></g>
                    <g class="towers">
                        <path class="t" d="M118 330 L150 70 L170 330 Z" fill="url(#gBlue)"/>
                        <path class="t" d="M150 70 L170 330 L182 330 Z" fill="#cfe6ff" opacity=".55"/>
                        <path class="t" d="M190 330 V150 L222 120 V330 Z" fill="url(#gSilver)"/>
                        <path class="t" d="M232 330 V110 H262 L282 92 V330 Z" fill="url(#gSilver)" opacity=".9"/>
                        <path class="t" d="M292 330 V180 L316 165 V330 Z" fill="url(#gBlue)" opacity=".85"/>
                        <g class="win">
                            <path d="M200 170h12M200 190h12M200 210h12M200 230h12M200 250h12M200 270h12M200 290h12"/>
                            <path d="M244 130h28M244 155h28M244 180h28M244 205h28M244 230h28M244 255h28M244 280h28M244 305h28"/>
                            <path d="M300 195h8M300 215h8M300 235h8M300 255h8M300 275h8M300 295h8"/>
                        </g>
                    </g>
                    <path d="M70 330 H350" class="ground"/>
                    <path d="M80 352c70-26 190-30 270-6" class="swoosh"/>
                    <text x="210" y="392" text-anchor="middle" class="art-label">{{ \Illuminate\Support\Str::upper(\App\Models\Setting::read('site_name', 'en')) }}</text>
                </svg>
            </div>
        </div>
    </section>

    {{-- KEY FIGURES: solid blocks that sit on the hero's lower edge --}}
    <section class="figures" aria-label="{{ __('Key figures') }}">
        <div class="container">
            <dl class="figures-grid">
                @foreach (range(1, 3) as $stat)
                    @if (site("stat_{$stat}_value"))
                        <div class="figure figure-{{ $loop->iteration }} reveal">
                            <dd data-count>{{ site("stat_{$stat}_value") }}</dd>
                            <dt>{{ site("stat_{$stat}_label") }}</dt>
                        </div>
                    @endif
                @endforeach
                <div class="figure figure-call reveal">
                    <dd><a href="{{ site_phone_href() }}">{{ site('phone') }}</a></dd>
                    <dt>{{ __('Hotline') }} · {{ site('office_hours') }}</dt>
                </div>
            </dl>
        </div>
    </section>

    {{-- SERVICES --}}
    <section class="section" id="services">
        <div class="container">
            <header class="sec-head sec-head-split reveal">
                <div>
                    <p class="label">{{ __('Services') }}</p>
                    <h2>{{ site('services_title') }}</h2>
                </div>
                <p class="sub">{{ site('services_sub') }}</p>
            </header>

            <x-site.service-tiles :services="$services" />
        </div>
    </section>


    {{-- APPROACH: photo half and navy half --}}
    <section class="split-band">
        <div class="split-photo">
            @if (\App\Models\Setting::raw()['why_image'] ?? '')
                <img src="{{ \App\Models\Setting::imageUrl('why_image', 'images/fdh-logo.jpg') }}" alt="" loading="lazy">
            @endif
        </div>
        <div class="split-copy">
            <div class="split-copy-inner reveal">
                <p class="label">{{ __('Approach') }}</p>
                <h2>{{ site('why_title') }}</h2>
                <p class="sub">{{ site('why_sub') }}</p>
                <ol class="point-list">
                    @foreach (range(1, 4) as $n)
                        <li wire:key="why-{{ $n }}">
                            <span class="point-no">{{ app()->getLocale() === 'bn' ? \App\Support\BanglaNumber::digits('0'.$n) : '0'.$n }}</span>
                            <div>
                                <h3>{{ site("why_{$n}_title") }}</h3>
                                <p>{{ site("why_{$n}_text") }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
                <a href="{{ route('contact') }}" wire:navigate class="btn btn-amber">{{ site('hero_cta') }} <x-icon name="arrow" /></a>
            </div>
        </div>
    </section>

    {{-- PROJECTS --}}
    @if ($projects->isNotEmpty())
        <section class="section">
            <div class="container">
                <header class="sec-head sec-head-split reveal">
                    <div>
                        <p class="label">{{ __('Projects') }}</p>
                        <h2>{{ site('projects_title') }}</h2>
                    </div>
                    <a href="{{ route('projects') }}" wire:navigate class="btn btn-outline">{{ __('All projects') }} <x-icon name="arrow" /></a>
                </header>
                <div class="project-grid">
                    @foreach ($projects as $project)
                        <x-site.project-card :project="$project" wire:key="proj-{{ $project->id }}" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-site.process />

    {{-- BUILDING TYPES --}}
    <section class="section">
        <div class="container">
            <header class="sec-head reveal">
                <p class="label">{{ __('Scope') }}</p>
                <h2>{{ site('types_title') }}</h2>
            </header>
            <div class="types">
                @foreach (range(1, 6) as $n)
                    @if (site("type_{$n}_title"))
                        <div class="type reveal" wire:key="type-{{ $n }}">
                            <x-icon :name="['home', 'duplex', 'apartment', 'shop', 'interior', 'renovation'][$n - 1]" class="type-icon" />
                            <h3>{{ site("type_{$n}_title") }}</h3>
                            <p>{{ site("type_{$n}_text") }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    {{-- PRINCIPAL --}}
    <section class="section section-alt">
        <div class="container principal">
            <figure class="principal-photo reveal">
                @if (\App\Models\Setting::raw()['md_photo'] ?? '')
                    <img src="{{ \App\Models\Setting::imageUrl('md_photo', 'images/fdh-logo.jpg') }}" alt="{{ site('md_name') }}" loading="lazy">
                @else
                    <img src="{{ \App\Models\Setting::imageUrl('logo', 'images/fdh-logo.jpg') }}" alt="" class="principal-logo" loading="lazy">
                @endif
            </figure>
            <div class="principal-copy reveal">
                <p class="label">{{ __('Principal') }}</p>
                <blockquote class="pull">{{ site('about_lead') }}</blockquote>
                <div class="principal-sign">
                    <b>{{ site('md_name') }}</b>
                    <span>{{ site('md_title') }}</span>
                </div>
                <a href="{{ route('about') }}" wire:navigate class="btn btn-outline">{{ __('About the practice') }} <x-icon name="arrow" /></a>
            </div>
        </div>
    </section>

    {{-- TESTIMONIALS (only real ones added by the admin) --}}
    @if ($testimonials->isNotEmpty())
        <section class="section">
            <div class="container">
                <header class="sec-head reveal">
                    <p class="label">{{ __('Clients') }}</p>
                    <h2>{{ site('testimonials_title') }}</h2>
                </header>
                <div class="quotes">
                    @foreach ($testimonials as $testimonial)
                        <figure class="quote reveal" wire:key="t-{{ $testimonial->id }}">
                            <blockquote>{{ $testimonial->quote }}</blockquote>
                            <figcaption><b>{{ $testimonial->name }}</b>@if ($testimonial->subtitle)<span>{{ $testimonial->subtitle }}</span>@endif</figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    @if ($faqs->isNotEmpty())
        <section class="section">
            <div class="container faq-layout">
                <div class="faq-intro reveal">
                    <p class="label">{{ __('FAQ') }}</p>
                    <h2>{{ site('faq_title') }}</h2>
                    <p class="sub">{{ __('Answers to what landowners ask us most — design, approval, soil test and cost.') }}</p>
                    <div class="help-card">
                        <h3>{{ __('Still have a question?') }}</h3>
                        <p>{{ __('Call or send a WhatsApp message — we are happy to help with your plan.') }}</p>
                        <div class="help-actions">
                            <a href="{{ site_phone_href() }}" class="btn btn-amber btn-sm"><x-icon name="phone" /> {{ site('phone') }}</a>
                            <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-wa"><x-icon name="whatsapp" /> WhatsApp</a>
                        </div>
                    </div>
                    <a href="{{ route('faq') }}" wire:navigate class="text-link faq-more">{{ __('See all questions') }} <x-icon name="arrow" /></a>
                </div>
                <x-site.faq-list :faqs="$faqs" :open-first="true" />
            </div>
        </section>
    @endif

    {{-- CONTACT --}}
    <section class="section section-alt" id="contact">
        <div class="container split-2">
            <div class="reveal contact-intro">
                <p class="label">{{ __('Contact') }}</p>
                <h2>{{ site('contact_title') }}</h2>
                <p class="sub">{{ site('contact_sub') }}</p>
                @include('livewire.site.partials.contact-list')
            </div>
            <livewire:site.inquiry-form />
        </div>
    </section>
</div>
