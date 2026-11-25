<div>
    <x-site.page-hero :eyebrow="__('Services')" :title="site('services_title')" :sub="site('services_sub')" />

    <section class="section section-flush">
        <div class="container">
            <x-site.service-tiles :services="$services" />
        </div>
    </section>

    <x-site.process />
</div>
