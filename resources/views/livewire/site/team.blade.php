<div>
    <x-site.page-hero :eyebrow="__('Team')" :title="site('team_title')" :sub="__('Engineers and designers who will take care of your project from start to finish.')" />

    <section class="section section-flush">
        <div class="container">
            @if ($members->isEmpty())
                <div class="empty reveal"><h2>{{ __('Team profiles coming soon') }}</h2></div>
            @else
                <div class="people">
                    @foreach ($members as $member)
                        <x-site.team-card :member="$member" wire:key="tm-{{ $member->id }}" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

</div>
