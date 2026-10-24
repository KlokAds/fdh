<section class="section" id="process">
    <div class="container">
        <header class="sec-head reveal">
            <p class="label">{{ __('Process') }}</p>
            <h2>{{ site('process_title') }}</h2>
        </header>
        <ol class="steps">
            @foreach (['chat', 'survey', 'building', 'estimate', 'construction'] as $stepIcon)
                @php($step = $loop->iteration)
                <li class="reveal" wire:key="step-{{ $step }}">
                    <div class="steps-top">
                        <span class="steps-icon"><x-icon :name="$stepIcon" /></span>
                        <span class="steps-num">{{ __('Step') }} {{ \App\Support\CostCalculator::number($step) }}</span>
                    </div>
                    <h3>{{ site("process_{$step}_title") }}</h3>
                    <p>{{ site("process_{$step}_text") }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>
