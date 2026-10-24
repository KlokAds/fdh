<div class="form-wrap reveal">
    @if ($sent)
        <div class="form form-success" role="status">
            <p class="label">{{ __('Received') }}</p>
            <h3>{{ __('Thank you!') }}</h3>
            <p>{{ __('We’ve received your request and will call you soon. For a faster reply, message us on WhatsApp.') }}</p>
            <a href="{{ $this->whatsappLink() }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary"><x-icon name="whatsapp" class="ic" /> {{ __('Continue on WhatsApp') }}</a>
        </div>
    @else
        <form class="form" wire:submit="submit" novalidate>
            <div class="form-title">
                <h3>{{ __('Request a free consultation') }}</h3>
                <p>{{ __('Fields marked * are required.') }}</p>
            </div>
            <div class="hp" aria-hidden="true">
                <label>Website <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
            </div>
            <div class="row">
                <label>{{ __('Your name') }} *
                    <input wire:model="name" autocomplete="name" maxlength="100" placeholder="{{ __('e.g. Rahim Uddin') }}" @error('name') aria-invalid="true" @enderror>
                    @error('name') <span class="err">{{ $message }}</span> @enderror
                </label>
                <label>{{ __('Mobile number') }} *
                    <input wire:model="phone" type="tel" autocomplete="tel" maxlength="20" placeholder="01XXXXXXXXX" @error('phone') aria-invalid="true" @enderror>
                    @error('phone') <span class="err">{{ $message }}</span> @enderror
                </label>
            </div>
            <div class="row">
                <div class="field">
                    <span class="field-label" id="service-label">{{ __('Service needed') }}</span>
                    <details class="cselect" data-cselect>
                        <summary class="cselect-trigger" aria-labelledby="service-label" aria-haspopup="listbox">
                            <span class="cselect-icon"><x-icon :name="$choices[$service] ?? 'building'" /></span>
                            <span @class(['cselect-value', 'is-placeholder' => $service === ''])>{{ $service !== '' ? $service : __('Select a service') }}</span>
                            <x-icon name="chevron" class="cselect-chevron" />
                        </summary>
                        <ul class="cselect-list" role="listbox" aria-labelledby="service-label">
                            @foreach ($choices as $label => $icon)
                                <li wire:key="opt-{{ $loop->index }}">
                                    <button type="button" role="option" aria-selected="{{ $service === $label ? 'true' : 'false' }}" wire:click="chooseService({{ $loop->index }})" @class(['cselect-option', 'is-selected' => $service === $label])>
                                        <span class="cselect-icon"><x-icon :name="$icon" /></span>
                                        <span class="cselect-text">{{ $label }}</span>
                                        <x-icon name="check" class="cselect-check" />
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                    @error('service') <span class="err">{{ $message }}</span> @enderror
                </div>
                <label>{{ __('Project location') }}
                    <input wire:model="location" maxlength="150" placeholder="{{ __('e.g. Rangpur Sadar') }}">
                    @error('location') <span class="err">{{ $message }}</span> @enderror
                </label>
            </div>
            <label>{{ __('Email (optional)') }}
                <input wire:model="email" type="email" autocomplete="email" maxlength="150" placeholder="you@example.com">
                @error('email') <span class="err">{{ $message }}</span> @enderror
            </label>
            <label>{{ __('Tell us about your project') }}
                <textarea wire:model="message" rows="4" maxlength="2000" placeholder="{{ __('Land size, number of floors, budget, timeline…') }}"></textarea>
                @error('message') <span class="err">{{ $message }}</span> @enderror
            </label>
            @error('form') <p class="form-error" role="alert">{{ $message }}</p> @enderror
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">{{ __('Send request') }}</span>
                    <span wire:loading wire:target="submit">{{ __('Sending…') }}</span>
                </button>
                <span class="form-note">{{ __('Your details are only used to contact you.') }}</span>
            </div>
        </form>
    @endif
</div>
