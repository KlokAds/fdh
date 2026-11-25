{{-- An uploaded photo with its resized copies, so each screen downloads one close to the size it shows. --}}
@props(['path' => null, 'src', 'sizes' => '100vw', 'srcset' => null])
@php($srcset ??= \App\Support\ImageStore::srcset($path))
<img src="{{ $src }}" @if ($srcset) srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif {{ $attributes }}>
