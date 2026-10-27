<div class="offices">
    @foreach (['rangpur' => ['Head Office', 'Rangpur'], 'dhaka' => ['Branch', 'Dhaka']] as $key => [$kind, $city])
        @if (site("{$key}_address"))
            <article class="office-card reveal" wire:key="office-{{ $key }}">
                <div class="office-map">
                    <iframe src="https://www.google.com/maps?q={{ rawurlencode(\App\Models\Setting::read("{$key}_address", 'en')) }}&amp;output=embed" title="{{ __('Map') }} — {{ __($city) }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <div class="office-top">
                    <span class="cc-icon"><x-icon name="pin" /></span>
                    <span class="office-kind">{{ __($kind) }}</span>
                </div>
                <h3>{{ __($city) }}</h3>
                <p>{{ site("{$key}_address") }}</p>
                @if (site('office_hours'))
                    <p class="office-hours"><x-icon name="clock" /> {{ site('office_hours') }}</p>
                @endif
                <div class="office-actions">
                    <a href="https://www.google.com/maps/search/?api=1&query={{ rawurlencode(\App\Models\Setting::read("{$key}_address", 'en')) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm">{{ __('Get directions') }} ↗</a>
                    <a href="{{ site_phone_href() }}" class="btn btn-sm btn-outline"><x-icon name="phone" class="ic" /> {{ __('Call') }}</a>
                </div>
            </article>
        @endif
    @endforeach
</div>
