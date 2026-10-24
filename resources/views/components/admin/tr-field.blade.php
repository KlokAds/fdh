@props(['field', 'label', 'type' => 'text', 'required' => false, 'max' => 255, 'rows' => 4, 'hint' => null, 'model' => 'form'])
<fieldset class="tr-field">
    <legend>{{ $label }} @if ($required)<span class="req">*</span>@endif @if ($hint)<small class="muted">{{ $hint }}</small>@endif</legend>
    <div class="tr-grid">
        @foreach (config('fdh.locales') as $locale => $meta)
            <label>
                <span class="lang-tag">{{ $meta['short'] }}</span>
                @if ($type === 'textarea')
                    <textarea wire:model="{{ $model }}.{{ $field }}.{{ $locale }}" rows="{{ $rows }}" maxlength="{{ $max }}" lang="{{ $locale }}"></textarea>
                @else
                    <input type="text" wire:model="{{ $model }}.{{ $field }}.{{ $locale }}" maxlength="{{ $max }}" lang="{{ $locale }}">
                @endif
                @error("{$model}.{$field}.{$locale}") <span class="err">{{ $message }}</span> @enderror
            </label>
        @endforeach
    </div>
</fieldset>
