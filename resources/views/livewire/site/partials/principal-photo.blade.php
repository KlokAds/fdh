{{-- The principal's portrait. Until one is uploaded, the featured project's render stands in
     (with its name), which looks far better than an enlarged logo. --}}
@if (\App\Models\Setting::raw()['md_photo'] ?? '')
    <x-site.photo :path="\App\Models\Setting::raw()['md_photo'] ?? null" :src="\App\Models\Setting::imageUrl('md_photo', 'images/fdh-logo.jpg')" :alt="site('md_name')" loading="lazy" sizes="(max-width: 900px) 90vw, 420px" />
@elseif (($showcase ?? null)?->coverUrl())
    <x-site.photo :path="$showcase->cover_image" :src="$showcase->coverUrl()" :alt="$showcase->title" loading="lazy" sizes="(max-width: 900px) 90vw, 420px" />
    <span class="principal-caption">{{ __('Our design') }} · {{ $showcase->title }}</span>
@else
    <x-site.photo :path="\App\Models\Setting::raw()['logo'] ?? null" :src="\App\Models\Setting::imageUrl('logo', 'images/fdh-logo.jpg')" alt="" class="principal-logo" width="304" height="304" loading="lazy" sizes="260px" />
@endif
