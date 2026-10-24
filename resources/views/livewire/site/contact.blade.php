<div>
    <x-site.page-hero :eyebrow="__('Contact')" :title="site('contact_title')" :sub="site('contact_sub')" />

    <section class="section section-flush">
        <div class="container split-2">
            <div class="reveal contact-intro">
                <p class="label">{{ __('Direct') }}</p>
                <h2>{{ __('Talk to the engineer directly') }}</h2>
                <p class="sub">{{ __('Call or WhatsApp for a quick answer, or send your details with the form and we will call you back.') }}</p>
                @include('livewire.site.partials.contact-list')
            </div>
            <livewire:site.inquiry-form :service="$service" :message="$estimate" />
        </div>
    </section>

    <section class="section section-alt">
        <div class="container">
            <header class="sec-head reveal"><p class="label">{{ __('Offices') }}</p><h2>{{ __('Visit us') }}</h2></header>
            @include('livewire.site.partials.offices')
        </div>
    </section>
</div>
