<div>
    {{-- HERO --}}
    <section class="hero">
        <div class="hero-grid" aria-hidden="true"></div>
        <div class="container hero-inner">
            <div class="hero-copy">
                <p class="kicker reveal">{{ site('hero_eyebrow') }}</p>
                <h1 class="display reveal">{{ site('hero_title_start') }} <em>{{ site('hero_title_highlight') }}</em> {{ site('hero_title_end') }}</h1>
                <p class="lead reveal">{{ site('hero_lead') }}</p>
                <div class="hero-actions reveal">
                    <a href="{{ route('contact') }}" wire:navigate class="btn">{{ site('hero_cta') }} <span aria-hidden="true">→</span></a>
                    <a href="{{ site_phone_href() }}" class="link-quiet">{{ __('or call') }} {{ site('phone') }}</a>
                </div>
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

        <div class="container">
            <dl class="facts-row">
                @foreach (range(1, 3) as $stat)
                    @if (site("stat_{$stat}_value"))
                        <div><dt>{{ site("stat_{$stat}_label") }}</dt><dd>{{ site("stat_{$stat}_value") }}</dd></div>
                    @endif
                @endforeach
                <div><dt>{{ __('Hotline') }}</dt><dd><a href="{{ site_phone_href() }}">{{ site('phone') }}</a></dd></div>
            </dl>
        </div>
    </section>

    @if (\App\Models\Setting::raw()['hero_image'] ?? '')
        <figure class="hero-figure reveal">
            <img src="{{ \App\Models\Setting::imageUrl('hero_image', 'images/fdh-logo.jpg') }}" alt="{{ site('site_name') }}">
        </figure>
    @endif

    {{-- SERVICES --}}
    <section class="section" id="services">
        <div class="container">
            <header class="sec-head reveal">
                <p class="label">{{ __('Services') }}</p>
                <div>
                    <h2>{{ site('services_title') }}</h2>
                    <p class="sub">{{ site('services_sub') }}</p>
                </div>
            </header>

            <ol class="index-list">
                @foreach ($services as $service)
                    <li wire:key="svc-{{ $service->id }}" class="reveal">
                        <a href="{{ route('services.show', $service) }}" wire:navigate class="index-row">
                            <span class="index-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="index-title">{{ $service->title }}</span>
                            <span class="index-text">
                                {{ $service->summary }}
                                @if ($service->featureList())
                                    <small>{{ implode(' · ', $service->featureList()) }}</small>
                                @endif
                            </span>
                            <span class="index-arrow" aria-hidden="true">→</span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- COST CALCULATOR teaser (only when the admin turned it on and set rates) --}}
    @if (\App\Support\CostCalculator::isAvailable())
        <section class="calc-band">
            <div class="container calc-band-inner reveal">
                <div>
                    <p class="label">{{ __('Cost calculator') }}</p>
                    <h2>{{ __('How much will your building cost?') }}</h2>
                    <p class="sub">{{ __('Land in katha, shotok or sq ft — get an approximate cost in a minute.') }}</p>
                </div>
                <a href="{{ route('calculator') }}" wire:navigate class="btn">{{ __('Calculate cost') }} <span aria-hidden="true">→</span></a>
            </div>
        </section>
    @endif

    {{-- PROJECTS (only real ones) --}}
    @if ($projects->isNotEmpty())
        <section class="section section-tight">
            <div class="container">
                <header class="sec-head reveal">
                    <p class="label">{{ __('Projects') }}</p>
                    <div class="sec-head-row">
                        <h2>{{ site('projects_title') }}</h2>
                        <a href="{{ route('projects') }}" wire:navigate class="link-arrow">{{ __('All projects') }} →</a>
                    </div>
                </header>
                <div class="project-grid">
                    @foreach ($projects as $project)
                        <x-site.project-card :project="$project" wire:key="proj-{{ $project->id }}" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- WHY --}}
    <section class="section section-navy">
        <div class="container why">
            <div class="why-intro reveal">
                <p class="label">{{ __('Approach') }}</p>
                <h2 class="statement">{{ site('why_title') }}</h2>
                <p class="sub">{{ site('why_sub') }}</p>
                <div class="why-badge">
                    <span class="why-badge-icon"><x-icon name="shield" /></span>
                    <span><b>{{ site('md_name') }}</b><small>{{ site('md_title') }}</small></span>
                </div>
                <div class="why-actions">
                    <a href="{{ route('contact') }}" wire:navigate class="btn btn-light">{{ site('hero_cta') }} <span aria-hidden="true">→</span></a>
                    <a href="{{ site_phone_href() }}" class="link-quiet">{{ __('or call') }} {{ site('phone') }}</a>
                </div>
            </div>
            <ol class="why-cards">
                @foreach (['shield', 'layers', 'chart', 'pin'] as $whyIcon)
                    <li class="why-card reveal" wire:key="why-{{ $loop->iteration }}">
                        <span class="why-icon"><x-icon :name="$whyIcon" /></span>
                        <h3>{{ site("why_{$loop->iteration}_title") }}</h3>
                        <p>{{ site("why_{$loop->iteration}_text") }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <x-site.process />

    {{-- BUILDING TYPES --}}
    <section class="section section-tight">
        <div class="container">
            <header class="sec-head reveal">
                <p class="label">{{ __('Scope') }}</p>
                <h2>{{ site('types_title') }}</h2>
            </header>
            <div class="types">
                @foreach (range(1, 6) as $n)
                    @if (site("type_{$n}_title"))
                        <div class="type reveal" wire:key="type-{{ $n }}">
                            <span class="type-icon"><x-icon :name="['home', 'duplex', 'apartment', 'shop', 'interior', 'renovation'][$n - 1]" /></span>
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
            <div class="reveal">
                <p class="label">{{ __('Principal') }}</p>
                <p class="pull">{{ site('about_lead') }}</p>
                <p class="principal-name">{{ site('md_name') }}</p>
                <p class="muted">{{ site('md_title') }}</p>
                <a href="{{ route('about') }}" wire:navigate class="link-arrow">{{ __('About the practice') }} →</a>
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
                            <blockquote>“{{ $testimonial->quote }}”</blockquote>
                            <figcaption><b>{{ $testimonial->name }}</b>@if ($testimonial->subtitle) · {{ $testimonial->subtitle }}@endif</figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    @if ($faqs->isNotEmpty())
        <section class="section">
            <div class="container split-2">
                <div class="reveal">
                    <p class="label">{{ __('FAQ') }}</p>
                    <h2>{{ site('faq_title') }}</h2>
                    <a href="{{ route('faq') }}" wire:navigate class="link-arrow">{{ __('All questions') }} →</a>
                    <div class="help-card">
                        <span class="help-icon"><x-icon name="chat" /></span>
                        <h3>{{ __('Still have a question?') }}</h3>
                        <p>{{ __('Call or send a WhatsApp message — we are happy to help with your plan.') }}</p>
                        <div class="help-actions">
                            <a href="{{ site_phone_href() }}" class="btn btn-sm"><x-icon name="phone" /> {{ site('phone') }}</a>
                            <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-wa"><x-icon name="whatsapp" /> WhatsApp</a>
                        </div>
                    </div>
                </div>
                <x-site.faq-list :faqs="$faqs" />
            </div>
        </section>
    @endif

    {{-- CONTACT --}}
    <section class="section section-alt" id="contact">
        <div class="container split-2">
            <div class="reveal">
                <p class="label">{{ __('Contact') }}</p>
                <h2>{{ site('contact_title') }}</h2>
                <p class="sub">{{ site('contact_sub') }}</p>
                @include('livewire.site.partials.contact-list')
            </div>
            <livewire:site.inquiry-form />
        </div>
    </section>
</div>
