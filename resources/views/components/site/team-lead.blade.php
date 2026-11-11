@props(['member'])
{{-- The head of the team: larger than everyone else, shown before the grid. --}}
<article {{ $attributes->class(['lead-card', 'reveal', 'is-blank' => ! $member->photoUrl()]) }}>
    @if ($member->photoUrl())
        <div class="lead-photo">
            <x-site.photo :path="$member->photo" :src="$member->photoUrl()" :alt="$member->name" sizes="(max-width: 760px) 100vw, 360px" />
        </div>
    @else
        <div class="lead-blank" aria-hidden="true">
            <span class="person-badge"><svg viewBox="0 0 48 48"><circle cx="24" cy="18" r="8" /><path d="M8 42c1.600-9 8-14 16-14s14.400 5 16 14" /></svg></span>
        </div>
    @endif
    <div class="lead-copy">
        <p class="label">{{ __('Head of the team') }}</p>
        <h3>{{ $member->name }}</h3>
        <p class="lead-role">{{ $member->designation }}</p>
        @if ($member->bio)
            <p class="lead-bio">{{ $member->bio }}</p>
        @endif
        @if ($member->phone || $member->email || $member->facebook || $member->linkedin)
            <p class="lead-links">
                @if ($member->phone)<a href="tel:{{ preg_replace('/[^\d+]/', '', $member->phone) }}">{{ $member->phone }}</a>@endif
                @if ($member->email)<a href="mailto:{{ $member->email }}">{{ __('Email') }}</a>@endif
                @if ($member->facebook)<a href="{{ $member->facebook }}" target="_blank" rel="noopener noreferrer">Facebook</a>@endif
                @if ($member->linkedin)<a href="{{ $member->linkedin }}" target="_blank" rel="noopener noreferrer">LinkedIn</a>@endif
            </p>
        @endif
    </div>
</article>
