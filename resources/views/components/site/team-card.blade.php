@props(['member'])
<article {{ $attributes->class(['person', 'reveal', 'is-blank' => ! $member->photoUrl()]) }}>
    @if ($member->photoUrl())
        <div class="person-photo">
            <x-site.photo :path="$member->photo" :src="$member->photoUrl()" :alt="$member->name" loading="lazy" sizes="(max-width: 760px) 50vw, 25vw" />
        </div>
    @else
        {{-- No photo yet: a drawing-sheet band with a line-drawn portrait badge. --}}
        <div class="person-band" aria-hidden="true">
            <span class="person-badge"><svg viewBox="0 0 48 48"><circle cx="24" cy="18" r="8" /><path d="M8 42c1.600-9 8-14 16-14s14.400 5 16 14" /></svg></span>
        </div>
    @endif
    <div class="person-body">
        <h3>{{ $member->name }}</h3>
        <p class="person-role">{{ $member->designation }}</p>
        @if ($member->bio)
            <p class="person-bio">{{ $member->bio }}</p>
        @endif
        @if ($member->phone || $member->email || $member->facebook || $member->linkedin)
            <p class="person-links">
                @if ($member->phone)<a href="tel:{{ preg_replace('/[^\d+]/', '', $member->phone) }}">{{ $member->phone }}</a>@endif
                @if ($member->email)<a href="mailto:{{ $member->email }}">{{ __('Email') }}</a>@endif
                @if ($member->facebook)<a href="{{ $member->facebook }}" target="_blank" rel="noopener noreferrer">Facebook</a>@endif
                @if ($member->linkedin)<a href="{{ $member->linkedin }}" target="_blank" rel="noopener noreferrer">LinkedIn</a>@endif
            </p>
        @endif
    </div>
</article>
