<div>
    <x-site.page-hero :eyebrow="__('Projects')" :title="$project->title" :sub="collect([__($project->category), $project->location, $project->year])->filter()->implode(' · ')" />

    @if ($project->coverUrl())
        <figure class="hero-figure reveal">
            <x-site.photo :path="$project->cover_image" :src="$project->coverUrl()" :alt="$project->title" fetchpriority="high" sizes="(max-width: 1100px) 100vw, 1100px" />
        </figure>
    @endif

    <section class="section">
        <div class="container detail">
            <article class="prose reveal">
                {{ \App\Support\RichText::render($project->description) }}
            </article>
            <aside class="aside reveal">
                <dl class="spec">
                    <div><dt>{{ __('Status') }}</dt><dd><span @class(['status-chip', 'is-'.$project->status])>{{ __(\App\Models\Project::STATUSES[$project->status] ?? $project->status) }}</span></dd></div>
                    <div><dt>{{ __('Category') }}</dt><dd>{{ __($project->category) }}</dd></div>
                    @if ($project->floors)<div><dt>{{ __('Storeys') }}</dt><dd>{{ \App\Support\BanglaNumber::number($project->floors) }}</dd></div>@endif
                    @if ($project->land_katha)<div><dt>{{ __('Land') }}</dt><dd>{{ app()->getLocale() === 'bn' ? \App\Support\BanglaNumber::digits(rtrim(rtrim((string) $project->land_katha, '0'), '.')) : rtrim(rtrim((string) $project->land_katha, '0'), '.') }} {{ __('katha') }}</dd></div>@endif
                    @if ($project->floor_area_sqft)<div><dt>{{ __('Built area') }}</dt><dd>{{ \App\Support\BanglaNumber::number($project->floor_area_sqft) }} {{ __('sq ft') }}</dd></div>@endif
                    @if ($project->location)<div><dt>{{ __('Location') }}</dt><dd>{{ $project->location }}</dd></div>@endif
                    @if ($project->year)<div><dt>{{ __('Year') }}</dt><dd>{{ $project->year }}</dd></div>@endif
                </dl>
                <a href="{{ route('contact') }}" wire:navigate class="btn btn-block">{{ __('Plan a similar project') }} <span aria-hidden="true">→</span></a>
            </aside>
        </div>

        @if ($project->galleryUrls())
            <div class="container gallery">
                @foreach ($project->galleryUrls() as $i => $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener" wire:key="g-{{ $loop->index }}" class="reveal"><x-site.photo :path="array_values($project->gallery ?? [])[$i] ?? null" :src="$url" :alt="$project->title.' — '.$loop->iteration" loading="lazy" sizes="(max-width: 760px) 100vw, 33vw" /></a>
                @endforeach
            </div>
        @endif
    </section>

    @if ($related->isNotEmpty())
        <section class="section section-alt">
            <div class="container">
                <header class="sec-head reveal"><p class="label">{{ __('More work') }}</p><h2>{{ __('Other projects') }}</h2></header>
                <div class="project-grid">
                    @foreach ($related as $item)
                        <x-site.project-card :project="$item" wire:key="rel-{{ $item->id }}" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
