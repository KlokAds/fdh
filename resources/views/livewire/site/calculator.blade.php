<div>
    <x-site.page-hero :eyebrow="__('Cost calculator')" :title="__('How much will your building cost?')" :sub="__('Enter your land size, floors and finishing level for an approximate construction cost.')" />

    <section class="section section-flush">
        <div class="container calc">
            <form class="calc-form reveal" wire:submit.prevent novalidate>
                <div class="calc-field">
                    <span class="field-label" id="land-label">{{ __('Land size') }}</span>
                    <div class="calc-land">
                        <input type="number" inputmode="decimal" min="0" step="0.5" wire:model.live.debounce.300ms="landSize" aria-labelledby="land-label">
                        <div class="seg" role="group" aria-label="{{ __('Unit') }}">
                            @foreach (array_keys(\App\Support\CostCalculator::LAND_UNITS) as $u)
                                <button type="button" wire:key="unit-{{ $u }}" wire:click="$set('unit', '{{ $u }}')" @class(['is-active' => $unit === $u])>{{ __(ucfirst($u)) }}</button>
                            @endforeach
                        </div>
                    </div>
                    <small class="muted">{{ __('1 katha = 720 sq ft · 1 shotok (decimal) = 435.6 sq ft') }}</small>
                </div>

                <div class="calc-field">
                    <span class="field-label">{{ __('Number of floors') }}</span>
                    <div class="stepper">
                        <button type="button" wire:click="changeFloors(-1)" aria-label="{{ __('Fewer floors') }}">−</button>
                        <output>{{ \App\Support\CostCalculator::number($floors) }}</output>
                        <button type="button" wire:click="changeFloors(1)" aria-label="{{ __('More floors') }}">+</button>
                    </div>
                </div>

                <div class="calc-field">
                    <span class="field-label">{{ __('Finishing level') }}</span>
                    <div class="levels">
                        @foreach ($rates as $lv => $rate)
                            <button type="button" wire:key="lv-{{ $lv }}" wire:click="setLevel('{{ $lv }}')" @class(['level', 'is-active' => $level === $lv])>
                                <b>{{ __(ucfirst($lv)) }}</b>
                                <small>{{ \App\Support\CostCalculator::taka($rate) }} / {{ __('sq ft') }}</small>
                            </button>
                        @endforeach
                    </div>
                </div>
            </form>

            <aside class="calc-result reveal" aria-live="polite">
                @if ($result)
                    <p class="label">{{ __('Approximate construction cost') }}</p>
                    <p class="calc-total">{{ \App\Support\CostCalculator::taka($result['low']) }} – {{ \App\Support\CostCalculator::taka($result['high']) }}</p>
                    <dl class="spec">
                        <div><dt>{{ __('Land') }}</dt><dd>{{ \App\Support\CostCalculator::number($result['land_sqft']) }} {{ __('sq ft') }}</dd></div>
                        <div><dt>{{ __('Built area per floor') }}</dt><dd>{{ \App\Support\CostCalculator::number($result['floor_sqft']) }} {{ __('sq ft') }}</dd></div>
                        <div><dt>{{ __('Total built area') }}</dt><dd>{{ \App\Support\CostCalculator::number($result['total_sqft']) }} {{ __('sq ft') }}</dd></div>
                        <div><dt>{{ __('Rate') }}</dt><dd>{{ \App\Support\CostCalculator::taka($result['rate']) }} / {{ __('sq ft') }}</dd></div>
                    </dl>
                    <p class="calc-note">{{ site('calc_note') }}</p>
                    <a href="{{ route('contact', ['estimate' => $summary]) }}" wire:navigate class="btn btn-block">{{ __('Get an exact estimate') }} <span aria-hidden="true">→</span></a>
                @else
                    <p class="label">{{ __('Approximate construction cost') }}</p>
                    <p class="muted">{{ __('Enter a land size to see the estimate.') }}</p>
                @endif
            </aside>
        </div>
    </section>

    <x-site.cta />
</div>
