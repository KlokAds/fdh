<div class="contact-cards">
    <a href="{{ site_phone_href() }}" class="contact-card">
        <span class="cc-icon"><x-icon name="phone" /></span>
        <span class="cc-body">
            <small>{{ __('Call us') }}</small>
            <b>{{ site('phone') }}</b>
        </span>
        <span class="cc-go" aria-hidden="true"><x-icon name="arrow" /></span>
    </a>
    <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener noreferrer" class="contact-card cc-whatsapp">
        <span class="cc-icon"><x-icon name="whatsapp" /></span>
        <span class="cc-body">
            <small>WhatsApp · {{ __('Usually replies the same day') }}</small>
            <b>{{ __('Message us') }}</b>
        </span>
        <span class="cc-go" aria-hidden="true"><x-icon name="arrow" /></span>
    </a>
    <a href="mailto:{{ site('email') }}" class="contact-card">
        <span class="cc-icon"><x-icon name="mail" /></span>
        <span class="cc-body">
            <small>{{ __('Email') }}</small>
            <b class="cc-break">{{ site('email') }}</b>
        </span>
        <span class="cc-go" aria-hidden="true"><x-icon name="arrow" /></span>
    </a>
    @if (site('office_hours'))
        <div class="contact-card cc-static">
            <span class="cc-icon"><x-icon name="clock" /></span>
            <span class="cc-body">
                <small>{{ __('Office hours') }}</small>
                <b>{{ site('office_hours') }}</b>
            </span>
        </div>
    @endif
</div>
