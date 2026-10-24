@props(['faqs'])
<div class="faq-list">
    @foreach ($faqs as $faq)
        <details class="faq reveal" wire:key="faq-{{ $faq->id }}">
            <summary><span>{{ $faq->question }}</span><x-icon name="plus" class="faq-icon" /></summary>
            <div class="faq-answer">
                @foreach (preg_split('/\n\s*\n/', (string) $faq->answer) as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
        </details>
    @endforeach
</div>
