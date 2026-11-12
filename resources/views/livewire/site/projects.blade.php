<div>
    <x-site.page-hero :eyebrow="__('Projects')" :title="__('Our projects')" :sub="__('A selection of homes, buildings and interiors we have designed and supervised.')" />

    <section class="section section-flush">
        <div class="container">
            @if ($categories->count() > 1)
                <div class="filters" role="tablist" aria-label="{{ __('Filter projects') }}">
                    <button type="button" wire:click="filter('')" @class(['filter', 'is-active' => $active === ''])>{{ __('All') }}</button>
                    @foreach ($categories as $category)
                        <button type="button" wire:key="cat-{{ $loop->index }}" wire:click="filter(@js($category))" @class(['filter', 'is-active' => $active === $category])>{{ __($category) }}</button>
                    @endforeach
                </div>
            @endif

            @if ($projects->isEmpty())
                <div class="empty reveal">
                    <p class="label">{{ __('Coming soon') }}</p>
                    <h2>{{ __('Project gallery coming soon') }}</h2>
                    <p>{{ __('We’re adding photos of our completed work. Meanwhile, see our latest projects on our Facebook page.') }}</p>
                    @if (site('facebook'))
                        <a href="{{ site('facebook') }}" target="_blank" rel="noopener noreferrer" class="link-arrow">{{ __('Visit Facebook page') }} ↗</a>
                    @endif
                </div>
            @else
                <div class="project-grid" wire:loading.class="is-loading">
                    @foreach ($projects as $project)
                        <x-site.project-card :project="$project" wire:key="proj-{{ $project->id }}" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

</div>
