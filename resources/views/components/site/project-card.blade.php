@props(['project', 'large' => false])
<a href="{{ route('projects.show', $project) }}" wire:navigate {{ $attributes->class(['project-card', 'reveal', 'is-large' => $large]) }}>
    <div class="project-media">
        @if ($project->coverUrl())
            <img src="{{ $project->coverUrl() }}" alt="{{ $project->title }}" loading="lazy">
        @endif
        @if ($project->status && $project->status !== 'completed')
            <span @class(['status-chip', 'is-'.$project->status])>{{ __(\App\Models\Project::STATUSES[$project->status] ?? '') }}</span>
        @endif
        <span class="project-go" aria-hidden="true"><x-icon name="arrow" /></span>
    </div>
    <div class="project-meta">
        <span class="project-cat">{{ __($project->category) }}</span>
        <h3>{{ $project->title }}</h3>
        <p>{{ collect([$project->floors ? \App\Support\BanglaNumber::number($project->floors).' '.__('storey') : null, $project->location, $project->year])->filter()->implode(' · ') }}</p>
    </div>
</a>
