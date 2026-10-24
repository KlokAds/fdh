@props(['project', 'large' => false])
<a href="{{ route('projects.show', $project) }}" wire:navigate @class(['project-card', 'reveal', 'is-large' => $large])>
    <div class="project-media">
        @if ($project->coverUrl())
            <img src="{{ $project->coverUrl() }}" alt="{{ $project->title }}" loading="lazy">
        @endif
        @if ($project->status && $project->status !== 'completed')
            <span @class(['status-chip', 'is-'.$project->status])>{{ __(\App\Models\Project::STATUSES[$project->status] ?? '') }}</span>
        @endif
    </div>
    <div class="project-meta">
        <h3>{{ $project->title }}</h3>
        <p>{{ collect([__($project->category), $project->floors ? \App\Support\CostCalculator::number($project->floors).' '.__('storey') : null, $project->location, $project->year])->filter()->implode(' · ') }}</p>
    </div>
</a>
