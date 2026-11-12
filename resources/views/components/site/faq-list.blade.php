@props(['faqs', 'openFirst' => false])
<div class="faq-list">
    @foreach ($faqs as $faq)
        @php($number = str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT))
        <details class="faq reveal" wire:key="faq-{{ $faq->id }}" @if ($openFirst && $loop->first) open @endif>
            <summary>
                <span class="faq-num">{{ app()->getLocale() === 'bn' ? \App\Support\BanglaNumber::digits($number) : $number }}</span>
                <span class="faq-q">{{ $faq->question }}</span>
                <x-icon name="plus" class="faq-icon" />
            </summary>
            <div class="faq-answer">
                {{ \App\Support\RichText::render($faq->answer) }}
            </div>
        </details>
    @endforeach
</div>
