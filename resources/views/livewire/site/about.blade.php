<div>
    <x-site.page-hero :eyebrow="__('About')" :title="site('about_title')" :sub="site('about_lead')" />

    @if (\App\Models\Setting::raw()['about_image'] ?? '')
        <figure class="hero-figure reveal">
            <img src="{{ \App\Models\Setting::imageUrl('about_image', 'images/fdh-logo.jpg') }}" alt="{{ site('site_name') }}">
        </figure>
    @endif

    <section class="section">
        <div class="container split-2">
            <div class="reveal">
                <p class="label">{{ __('The practice') }}</p>
                <div class="prose">
                    @foreach (preg_split('/\n\s*\n/', site('about_body')) as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>
            </div>
            <div class="principal-card reveal">
                <div class="principal-photo">
                    @if (\App\Models\Setting::raw()['md_photo'] ?? '')
                        <img src="{{ \App\Models\Setting::imageUrl('md_photo', 'images/fdh-logo.jpg') }}" alt="{{ site('md_name') }}" loading="lazy">
                    @else
                        <span class="principal-initial" aria-hidden="true">{{ mb_substr(preg_replace('/^(Engr\.|ইঞ্জি\.)\s*/u', '', site('md_name')) ?: 'F', 0, 1) }}</span>
                    @endif
                </div>
                <p class="principal-name">{{ site('md_name') }}</p>
                <p class="muted">{{ site('md_title') }}</p>
            </div>
        </div>
    </section>

    <section class="section section-navy">
        <div class="container split-2">
            <div class="reveal">
                <p class="label">{{ __('Approach') }}</p>
                <h2 class="statement">{{ site('why_title') }}</h2>
            </div>
            <ol class="points">
                @foreach (range(1, 4) as $n)
                    <li class="reveal" wire:key="val-{{ $n }}">
                        <span class="points-num">{{ str_pad((string) $n, 2, '0', STR_PAD_LEFT) }}</span>
                        <div><h3>{{ site("why_{$n}_title") }}</h3><p>{{ site("why_{$n}_text") }}</p></div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    @if ($members->isNotEmpty())
        <section class="section">
            <div class="container">
                <header class="sec-head reveal"><p class="label">{{ __('Team') }}</p><h2>{{ site('team_title') }}</h2></header>
                <div class="people">
                    @foreach ($members as $member)
                        <x-site.team-card :member="$member" wire:key="tm-{{ $member->id }}" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section section-alt" id="offices">
        <div class="container">
            <header class="sec-head reveal"><p class="label">{{ __('Offices') }}</p><h2>{{ __('Visit us') }}</h2></header>
            @include('livewire.site.partials.offices')
        </div>
    </section>

</div>
