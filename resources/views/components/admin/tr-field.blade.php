@props(['field', 'label', 'type' => 'text', 'required' => false, 'max' => 255, 'rows' => 4, 'hint' => null, 'model' => 'form'])
<fieldset @class(['tr-field', 'tr-field-rich' => $type === 'rich'])>
    <legend>{{ $label }} @if ($required)<span class="req">*</span>@endif @if ($hint)<small class="muted">{{ $hint }}</small>@endif</legend>
    <div class="tr-grid">
        @foreach (config('fdh.locales') as $locale => $meta)
            @if ($type === 'rich')
                <div class="tr-rich" wire:key="rte-{{ $model }}-{{ $field }}-{{ $locale }}">
                    <span class="lang-tag">{{ $meta['short'] }}</span>
                    <x-admin.rich-editor :model="$model.'.'.$field.'.'.$locale" :lang="$locale" :max="$max" />
                    @error("{$model}.{$field}.{$locale}") <span class="err">{{ $message }}</span> @enderror
                </div>
            @else
                <label>
                    <span class="lang-tag">{{ $meta['short'] }}</span>
                    @if ($type === 'textarea')
                        <textarea wire:model="{{ $model }}.{{ $field }}.{{ $locale }}" rows="{{ $rows }}" maxlength="{{ $max }}" lang="{{ $locale }}"></textarea>
                    @else
                        <input type="text" wire:model="{{ $model }}.{{ $field }}.{{ $locale }}" maxlength="{{ $max }}" lang="{{ $locale }}">
                    @endif
                    @error("{$model}.{$field}.{$locale}") <span class="err">{{ $message }}</span> @enderror
                </label>
            @endif
        @endforeach
    </div>
</fieldset>
