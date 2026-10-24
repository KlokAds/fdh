<div>
    <x-site.page-hero :eyebrow="__('Services')" :title="$service->title" :sub="$service->summary" />

    <section class="section section-flush">
        <div class="container detail">
            <article class="prose reveal">
                @foreach (preg_split('/\n\s*\n/', (string) $service->description) as $paragraph)
                    @if (trim($paragraph) !== '')
                        <p>{{ $paragraph }}</p>
                    @endif
                @endforeach

                @if ($service->featureList())
                    <h2 class="h-small">{{ __('What’s included') }}</h2>
                    <ul class="ticks">
                        @foreach ($service->featureList() as $feature)
                            <li>{{ $feature }}</li>
                        @endforeach
                    </ul>
                @endif
            </article>

            <aside class="aside reveal">
                <div class="aside-block">
                    <p class="label">{{ __('Start here') }}</p>
                    <p>{{ __('Tell us about your project and an engineer will call you back.') }}</p>
                    <a href="{{ route('contact', ['service' => $service->title]) }}" wire:navigate class="btn btn-block">{{ __('Request this service') }} <span aria-hidden="true">→</span></a>
                    <a href="{{ site_phone_href() }}" class="link-quiet">{{ __('or call') }} {{ site('phone') }}</a>
                </div>
                @if ($others->isNotEmpty())
                    <div class="aside-block">
                        <p class="label">{{ __('Other services') }}</p>
                        <ul class="aside-links">
                            @foreach ($others as $other)
                                <li wire:key="other-{{ $other->id }}"><a href="{{ route('services.show', $other) }}" wire:navigate>{{ $other->title }} <span aria-hidden="true">→</span></a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </aside>
        </div>
    </section>

    <x-site.cta />
</div>
