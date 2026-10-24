<section class="section process-band" id="process">
    <div class="container">
        <header class="sec-head sec-head-split reveal">
            <div>
                <p class="label">{{ __('Process') }}</p>
                <h2>{{ site('process_title') }}</h2>
            </div>
            <p class="sub">{{ __('From the first call to handing over the keys — five clear steps, one responsible team.') }}</p>
        </header>
        <ol class="steps">
            @foreach (['chat', 'survey', 'building', 'estimate', 'construction'] as $stepIcon)
                @php($step = $loop->iteration)
                <li class="reveal" wire:key="step-{{ $step }}">
                    <div class="step-head">
                        <span class="step-no">{{ app()->getLocale() === 'bn' ? \App\Support\CostCalculator::bnDigits('0'.$step) : '0'.$step }}</span>
                        <x-icon :name="$stepIcon" class="step-icon" />
                    </div>
                    <h3>{{ site("process_{$step}_title") }}</h3>
                    <p>{{ site("process_{$step}_text") }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>
