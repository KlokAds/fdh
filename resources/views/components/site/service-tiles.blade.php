@props(['services'])
<div class="service-grid">
    @foreach ($services as $service)
        @php($number = str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT))
        <a href="{{ route('services.show', $service) }}" wire:navigate class="service-tile reveal" wire:key="svc-{{ $service->id }}">
            <span class="service-no">{{ app()->getLocale() === 'bn' ? \App\Support\BanglaNumber::digits($number) : $number }}</span>
            <x-icon :name="$service->icon ?: 'building'" class="service-icon" />
            <h3>{{ $service->title }}</h3>
            <p>{{ $service->summary }}</p>
            @if ($service->featureList())
                <ul class="service-features">
                    @foreach (array_slice($service->featureList(), 0, 3) as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>
            @endif
            <span class="service-more">{{ __('Details') }} <x-icon name="arrow" /></span>
        </a>
    @endforeach
    <div class="service-tile service-call reveal">
        <x-icon name="phone" class="service-icon" />
        <h3>{{ __('Not sure what you need?') }}</h3>
        <p>{{ __('Tell the engineer about your land and plan — we will tell you where to start.') }}</p>
        <a href="{{ site_phone_href() }}" class="btn btn-amber">{{ site('phone') }}</a>
    </div>
</div>
