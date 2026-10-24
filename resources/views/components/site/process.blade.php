<section class="section" id="process">
    <div class="container">
        <header class="sec-head reveal">
            <p class="label">{{ __('Process') }}</p>
            <h2>{{ site('process_title') }}</h2>
        </header>
        <ol class="steps">
            @foreach (range(1, 5) as $step)
                <li class="reveal" wire:key="step-{{ $step }}">
                    <span class="steps-num">{{ str_pad((string) $step, 2, '0', STR_PAD_LEFT) }}</span>
                    <h3>{{ site("process_{$step}_title") }}</h3>
                    <p>{{ site("process_{$step}_text") }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>
