@props(['eyebrow', 'title', 'sub' => null, 'image' => null])
@php($banner = $image ?? ((\App\Models\Setting::raw()['hero_image'] ?? '') ? \App\Models\Setting::imageUrl('hero_image', 'images/fdh-logo.jpg') : null))
<section class="page-hero">
    @if ($banner)
        <x-site.photo class="page-hero-photo" :path="$image ? null : (\App\Models\Setting::raw()['hero_image'] ?? null)" :src="$banner" alt="" fetchpriority="high" sizes="100vw" />
    @endif
    <div class="page-hero-shade" aria-hidden="true"></div>
    <div class="container page-hero-inner">
        <nav class="crumbs" aria-label="{{ __('Breadcrumb') }}">
            <a href="{{ route('home') }}" wire:navigate>{{ __('Home') }}</a><span aria-hidden="true">/</span><span>{{ $eyebrow }}</span>
        </nav>
        <h1 class="display display-sm">{{ $title }}</h1>
        @if ($sub)
            <p class="lead">{{ $sub }}</p>
        @endif
        {{ $slot }}
    </div>
</section>
