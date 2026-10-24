<div class="stack-lg">
    <nav class="tabs" aria-label="Content sections">
        @foreach ($groups as $key => $group)
            <button type="button" wire:key="tab-{{ $key }}" wire:click="$set('tab', '{{ $key }}')" @class(['tab', 'is-active' => $tab === $key])>{{ $group['label'] }}</button>
        @endforeach
    </nav>

    <section class="card">
        <form wire:submit="save" class="stack">
            @foreach ($fields as $key => $field)
                <div wire:key="field-{{ $key }}">
                    @if (! empty($field['translatable']))
                        <x-admin.tr-field model="values" :field="$key" :label="$field['label']" :type="$field['type']" :required="true" :max="$field['type'] === 'textarea' ? 3000 : 255" :rows="$field['type'] === 'textarea' ? 4 : 2" />
                    @elseif ($field['type'] === 'image')
                        <div class="image-field">
                            <label>{{ $field['label'] }}
                                <input type="file" wire:model="uploads.{{ $key }}" accept="image/jpeg,image/png,image/webp">
                                <span wire:loading wire:target="uploads.{{ $key }}" class="muted small">Uploading…</span>
                                @error("uploads.{$key}") <span class="err">{{ $message }}</span> @enderror
                            </label>
                            @if (! empty($values[$key]))
                                <div class="thumbs">
                                    <figure>
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($values[$key]) }}" alt="">
                                        <button type="button" class="thumb-x" wire:click="removeImage('{{ $key }}')" wire:confirm="Remove this image and use the default?" aria-label="Remove image">✕</button>
                                    </figure>
                                </div>
                            @else
                                <p class="muted small">Using the default image.</p>
                            @endif
                        </div>
                    @elseif ($field['type'] === 'secret')
                        <label>{{ $field['label'] }}
                            <input type="password" wire:model="secrets.{{ $key }}" autocomplete="off" maxlength="1000"
                                placeholder="{{ ! empty($savedSecrets[$key]) ? 'Saved — leave empty to keep the current value' : 'Not set' }}">
                            @error("secrets.{$key}") <span class="err">{{ $message }}</span> @enderror
                        </label>
                        @if (! empty($savedSecrets[$key]))
                            <p class="muted small">A value is saved (hidden for security). <button type="button" class="link-btn" wire:click="clearSecret('{{ $key }}')" wire:confirm="Remove the saved value?">Remove it</button></p>
                        @endif
                    @elseif ($field['type'] === 'color')
                        <label>{{ $field['label'] }}
                            <span class="color-field">
                                <input type="color" wire:model.live="values.{{ $key }}">
                                <input type="text" wire:model.live="values.{{ $key }}" maxlength="7" class="mono">
                            </span>
                            @error("values.{$key}") <span class="err">{{ $message }}</span> @enderror
                        </label>
                    @elseif ($field['type'] === 'select')
                        <label>{{ $field['label'] }}
                            <select wire:model="values.{{ $key }}">
                                @foreach ($field['options'] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error("values.{$key}") <span class="err">{{ $message }}</span> @enderror
                        </label>
                    @else
                        <label>{{ $field['label'] }}
                            <input type="{{ in_array($field['type'], ['email', 'url'], true) ? $field['type'] : 'text' }}" wire:model="values.{{ $key }}" maxlength="255">
                            @error("values.{$key}") <span class="err">{{ $message }}</span> @enderror
                        </label>
                    @endif
                </div>
            @endforeach

            <div class="row-actions sticky-actions">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">Save {{ strtolower($groups[$tab]['label'] ?? '') }}</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn btn-ghost">Preview website ↗</a>
            </div>
        </form>
    </section>
</div>
