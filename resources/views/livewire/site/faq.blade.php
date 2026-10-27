<div>
    <x-site.page-hero :eyebrow="__('FAQ')" :title="site('faq_title')" :sub="__('Answers to the questions clients ask us most. Can’t find yours? Just ask us.')" />

    <section class="section section-flush">
        <div class="container detail">
            @if ($faqs->isEmpty())
                <div class="empty reveal"><h2>{{ __('No questions yet') }}</h2></div>
            @else
                <x-site.faq-list :faqs="$faqs" />
            @endif
            <aside class="aside reveal">
                <div class="aside-block">
                    <p class="label">{{ __('Still have a question?') }}</p>
                    <p>{{ __('Call or message us — an engineer will answer.') }}</p>
                    <a href="{{ route('contact') }}" wire:navigate class="btn btn-block">{{ __('Ask us') }} <span aria-hidden="true">→</span></a>
                    <a href="{{ site_phone_href() }}" class="btn btn-outline btn-block"><x-icon name="phone" /> {{ site('phone') }}</a>
                </div>
            </aside>
        </div>
    </section>
</div>
