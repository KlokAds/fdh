@props(['project', 'large' => false])
<a href="{{ route('projects.show', $project) }}" wire:navigate @class(['project-card', 'reveal', 'is-large' => $large])>
    <div class="project-media">
        @if ($project->coverUrl())
            <img src="{{ $project->coverUrl() }}" alt="{{ $project->title }}" loading="lazy">
        @endif
    </div>
    <div class="project-meta">
        <h3>{{ $project->title }}</h3>
        <p>{{ collect([__($project->category), $project->location, $project->year])->filter()->implode(' · ') }}</p>
    </div>
</a>
