<section class="cta-band">
    <div class="container cta-inner reveal">
        <div>
            <h2>{{ site('cta_title') }}</h2>
            <p>{{ site('cta_sub') }}</p>
        </div>
        <div class="cta-actions">
            <a href="{{ route('contact') }}" wire:navigate class="btn btn-light">{{ __('Send your requirements') }} <span aria-hidden="true">→</span></a>
            <a href="{{ site_phone_href() }}" class="link-quiet">{{ site('phone') }}</a>
        </div>
    </div>
</section>
