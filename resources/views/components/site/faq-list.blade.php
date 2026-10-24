@props(['faqs'])
<div class="faq-list">
    @foreach ($faqs as $faq)
        <details class="faq reveal" wire:key="faq-{{ $faq->id }}">
            <summary><span>{{ $faq->question }}</span><x-icon name="plus" class="faq-icon" /></summary>
            <div class="faq-answer">
                {{ \App\Support\RichText::render($faq->answer) }}
            </div>
        </details>
    @endforeach
</div>
