@props(['eyebrow', 'title', 'sub' => null])
<section class="page-hero">
    <div class="container">
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
