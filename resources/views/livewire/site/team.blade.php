<div>
    <x-site.page-hero :eyebrow="__('Team')" :title="site('team_title')" :sub="__('Engineers and designers who will take care of your project from start to finish.')" />

    <section class="section section-flush">
        <div class="container">
            @if ($members->isEmpty())
                <div class="empty reveal"><h2>{{ __('Team profiles coming soon') }}</h2></div>
            @else
                @foreach ($members->where('is_lead', true) as $lead)
                    <x-site.team-lead :member="$lead" wire:key="tl-{{ $lead->id }}" />
                @endforeach
                <div class="people">
                    @foreach ($members->where('is_lead', false) as $member)
                        <x-site.team-card :member="$member" wire:key="tm-{{ $member->id }}" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

</div>
