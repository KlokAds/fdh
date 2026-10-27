{{-- The body and heading weights of the page's script, fetched alongside the stylesheet instead of after it. --}}
@php($fontScript = app()->getLocale() === 'bn' ? 'bn' : 'latin')
@foreach ([400, 700] as $fontWeight)
    <link rel="preload" href="{{ \Illuminate\Support\Facades\Vite::asset("resources/fonts/hind-siliguri-{$fontScript}-{$fontWeight}.woff2") }}" as="font" type="font/woff2" crossorigin>
@endforeach
