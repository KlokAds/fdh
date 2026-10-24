@props(['member'])
<article {{ $attributes->class(['person', 'reveal']) }}>
    <div class="person-photo">
        @if ($member->photoUrl())
            <img src="{{ $member->photoUrl() }}" alt="{{ $member->name }}" loading="lazy">
        @else
            <span class="person-initial" aria-hidden="true">{{ $member->initials() }}</span>
        @endif
    </div>
    <h3>{{ $member->name }}</h3>
    <p class="person-role">{{ $member->designation }}</p>
    @if ($member->bio)
        <p class="person-bio">{{ $member->bio }}</p>
    @endif
    <p class="person-links">
        @if ($member->phone)<a href="tel:{{ preg_replace('/[^\d+]/', '', $member->phone) }}">{{ $member->phone }}</a>@endif
        @if ($member->email)<a href="mailto:{{ $member->email }}">{{ __('Email') }}</a>@endif
        @if ($member->facebook)<a href="{{ $member->facebook }}" target="_blank" rel="noopener noreferrer">Facebook</a>@endif
        @if ($member->linkedin)<a href="{{ $member->linkedin }}" target="_blank" rel="noopener noreferrer">LinkedIn</a>@endif
    </p>
</article>
