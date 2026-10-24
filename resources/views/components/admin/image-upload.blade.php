@props([
    'model',
    'label',
    'upload' => null,
    'current' => null,
    'removeSaved' => null,
    'removeArg' => null,
    'hint' => 'JPG, PNG or WebP · up to 5 MB',
    'emptyNote' => null,
    'multiple' => false,
    'wide' => false,
])
@php
    $files = collect($multiple ? (array) $upload : [$upload])->filter()->values();
    $hasImage = $files->isNotEmpty() || ($current && ! $multiple);
@endphp
<div @class(['up', 'up-wide' => $wide])>
    <span class="field-label">{{ $label }}</span>

    @if ($hasImage || $multiple)
        <div class="up-grid">
            {{ $slot }}

            @foreach ($files as $index => $file)
                <figure class="up-tile is-new" wire:key="{{ $model }}-new-{{ $index }}">
                    @if (method_exists($file, 'isPreviewable') && $file->isPreviewable())
                        <img src="{{ $file->temporaryUrl() }}" alt="">
                    @endif
                    <span class="up-tag up-tag-new">New · saves with the form</span>
                    <button type="button" class="up-x" wire:click="discardUpload('{{ $model }}'{{ $multiple ? ", {$index}" : '' }})" aria-label="Remove this photo" title="Remove"><x-icon name="close" /></button>
                </figure>
            @endforeach

            @if (! $multiple && $files->isEmpty() && $current)
                <figure class="up-tile">
                    <img src="{{ $current }}" alt="">
                    <span class="up-tag"><x-icon name="check" /> Saved</span>
                    @if ($removeSaved)
                        <button type="button" class="up-x" wire:click="{{ $removeSaved }}{{ $removeArg !== null ? "('{$removeArg}')" : '' }}" wire:confirm="Remove this image?" aria-label="Remove this image" title="Remove"><x-icon name="close" /></button>
                    @endif
                </figure>
            @endif


            <label class="up-tile up-add">
                <input type="file" wire:model="{{ $model }}" accept="image/jpeg,image/png,image/webp" @if ($multiple) multiple @endif>
                <x-icon name="plus" />
                <span>{{ $multiple ? 'Add photos' : 'Change image' }}</span>
            </label>
        </div>
    @else
        <label class="up-drop">
            <input type="file" wire:model="{{ $model }}" accept="image/jpeg,image/png,image/webp">
            <span class="up-drop-icon"><x-icon name="upload" /></span>
            <b>Click to upload or drag an image here</b>
            <small>{{ $hint }}</small>
            @if ($emptyNote)<small class="up-note">{{ $emptyNote }}</small>@endif
        </label>
    @endif

    <div class="up-loading" wire:loading.flex wire:target="{{ $model }}"><span class="up-spin"></span> Uploading…</div>
    @error($model) <span class="err">{{ $message }}</span> @enderror
    @error("{$model}.*") <span class="err">{{ $message }}</span> @enderror
    @if ($hasImage || $multiple)<small class="muted small">{{ $hint }}</small>@endif
</div>
