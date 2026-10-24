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
                            <x-admin.image-upload
                                model="uploads.{{ $key }}"
                                :label="$field['label']"
                                :size="$field['size'] ?? null"
                                :upload="$uploads[$key] ?? null"
                                :current="! empty($values[$key]) ? \Illuminate\Support\Facades\Storage::disk('public')->url($values[$key]) : null"
                                :remove-saved="! empty($values[$key]) ? 'removeImage' : null"
                                :remove-arg="$key"
                                empty-note="Nothing uploaded — the website uses its default image."
                                :wide="in_array($key, ['hero_image', 'about_image', 'og_image'], true)"
                            />
                            @if ($samples->isSampleImage($key))
                                <p class="badge badge-warn">Sample photo — upload your own to replace it</p>
                            @endif
                        </div>
                    @elseif ($field['type'] === 'secret')
                        <label>{{ $field['label'] }}
                            <input type="password" wire:model="secrets.{{ $key }}" autocomplete="off" maxlength="1000"
                                placeholder="{{ ! empty($savedSecrets[$key]) ? 'Saved — leave empty to keep the current value' : 'Not set' }}">
                            @error("secrets.{$key}") <span class="err">{{ $message }}</span> @enderror
                        </label>
                        @if (! empty($savedSecrets[$key]))
                            <p class="muted small">A value is saved (hidden for security). <button type="button" class="link-btn" wire:click="clearSecret('{{ $key }}')" data-confirm="Remove the saved value?">Remove it</button></p>
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
                            <x-ui.select model="values.{{ $key }}" :options="$field['options']" :selected="$values[$key] ?? null" />
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
