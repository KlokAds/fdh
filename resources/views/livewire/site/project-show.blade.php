<div>
    <x-site.page-hero :eyebrow="__('Projects')" :title="$project->title" :sub="collect([__($project->category), $project->location, $project->year])->filter()->implode(' · ')" />

    @if ($project->coverUrl())
        <figure class="hero-figure reveal">
            <img src="{{ $project->coverUrl() }}" alt="{{ $project->title }}">
        </figure>
    @endif

    <section class="section">
        <div class="container detail">
            <article class="prose reveal">
                @foreach (preg_split('/\n\s*\n/', (string) $project->description) as $paragraph)
                    @if (trim($paragraph) !== '')
                        <p>{{ $paragraph }}</p>
                    @endif
                @endforeach
            </article>
            <aside class="aside reveal">
                <dl class="spec">
                    <div><dt>{{ __('Category') }}</dt><dd>{{ __($project->category) }}</dd></div>
                    @if ($project->location)<div><dt>{{ __('Location') }}</dt><dd>{{ $project->location }}</dd></div>@endif
                    @if ($project->year)<div><dt>{{ __('Year') }}</dt><dd>{{ $project->year }}</dd></div>@endif
                </dl>
                <a href="{{ route('contact') }}" wire:navigate class="btn btn-block">{{ __('Plan a similar project') }} <span aria-hidden="true">→</span></a>
            </aside>
        </div>

        @if ($project->galleryUrls())
            <div class="container gallery">
                @foreach ($project->galleryUrls() as $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener" wire:key="g-{{ $loop->index }}" class="reveal"><img src="{{ $url }}" alt="{{ $project->title }} — {{ $loop->iteration }}" loading="lazy"></a>
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
