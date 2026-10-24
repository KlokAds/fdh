<div>
    <x-site.page-hero :eyebrow="__('Services')" :title="site('services_title')" :sub="site('services_sub')" />

    <section class="section section-flush">
        <div class="container">
            <ol class="index-list index-list-lg">
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

    <x-site.process />
    <x-site.cta />
</div>
