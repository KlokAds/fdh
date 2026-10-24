<div class="stack-lg">
    @if ($showForm)
        <section class="card">
            <div class="card-head">
                <h2>{{ $editingId ? 'Edit project' : 'New project' }}</h2>
                <button type="button" class="link-btn" wire:click="cancel">Cancel ✕</button>
            </div>
            <form wire:submit="save" class="stack">
                <x-admin.tr-field field="title" label="Title" :required="true" max="150" />
                <x-admin.tr-field field="location" label="Location" max="150" />
                <x-admin.tr-field field="description" label="Description" hint="blank line = new paragraph" max="5000" type="textarea" rows="5" />
                <div class="row row-3">
                    <label>Category *
                        <select wire:model="category">
                            @foreach ($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                        @error('category') <span class="err">{{ $message }}</span> @enderror
                    </label>
                    <label>Year<input wire:model="year" maxlength="4" inputmode="numeric" placeholder="2026">@error('year') <span class="err">{{ $message }}</span> @enderror</label>
                    <label>Order<input type="number" wire:model="sort" min="0" max="999"></label>
                </div>
                <label>URL slug <small class="muted">(auto from English title)</small><input wire:model="slug" maxlength="150">@error('slug') <span class="err">{{ $message }}</span> @enderror</label>

                <x-admin.image-upload model="cover" :label="'Cover photo'.($editingId ? '' : ' *')" :upload="$cover" :current="$current?->coverUrl()" :wide="true" />

                <x-admin.image-upload model="photos" label="Gallery photos (up to 12)" :upload="$photos" :multiple="true" hint="JPG, PNG or WebP · max 5 MB each · resized, converted to WebP and stripped of location data automatically">
                    @foreach ($current?->galleryUrls() ?? [] as $url)
                        <figure class="up-tile" wire:key="gal-{{ $loop->index }}">
                            <img src="{{ $url }}" alt="">
                            <span class="up-tag"><x-icon name="check" /> Saved</span>
                            <button type="button" class="up-x" wire:click="removePhoto({{ $loop->index }})" wire:confirm="Remove this photo?" aria-label="Remove this photo" title="Remove"><x-icon name="close" /></button>
                        </figure>
                    @endforeach
                </x-admin.image-upload>

                <div class="checks-row">
                    <label class="check"><input type="checkbox" wire:model="is_published"> Published on website</label>
                    <label class="check"><input type="checkbox" wire:model="is_featured"> Featured (shown first)</label>
                </div>
                <div class="row-actions">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save,cover,photos">
                        <span wire:loading.remove wire:target="save">Save project</span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </button>
                </div>
            </form>
        </section>
    @endif

    <section class="card">
        <div class="card-head">
            <h2>All projects</h2>
            <button type="button" class="btn btn-primary btn-sm" wire:click="create">+ Add project</button>
        </div>
        @if ($projects->isEmpty())
            <p class="muted">No projects yet. Add your completed work with photos — it will appear on the Projects page.</p>
        @else
            <div class="admin-projects">
                @foreach ($projects as $project)
                    <article class="ap-card" wire:key="p-{{ $project->id }}">
                        <div class="ap-media">
                            @if ($project->coverUrl())<img src="{{ $project->coverUrl() }}" alt="" loading="lazy">@endif
                        </div>
                        <div class="ap-body">
                            <b>{{ $project->getTranslation('title', 'en') }} @if ($project->is_sample)<span class="pill pill-new">Sample — replace</span>@endif</b>
                            <span class="muted small">{{ $project->category }} · {{ $project->is_published ? 'Published' : 'Draft' }}{{ $project->is_featured ? ' · ★ Featured' : '' }}</span>
                            <div class="row-actions">
                                <button type="button" class="btn btn-ghost btn-sm" wire:click="edit({{ $project->id }})">Edit</button>
                                <button type="button" class="btn btn-danger btn-sm" wire:click="delete({{ $project->id }})" wire:confirm="Delete this project and its photos?">Delete</button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            {{ $projects->links('livewire.admin.partials.pagination') }}
        @endif
    </section>
</div>
