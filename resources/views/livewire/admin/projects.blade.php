<div class="stack-lg">
    @if ($showForm)
        <section class="card">
            <div class="card-head">
                <h2>{{ $editingId ? 'Edit project' : 'New project' }}</h2>
                <button type="button" class="close-btn" wire:click="cancel" title="Close" aria-label="Close form"><x-icon name="close" /></button>
            </div>
            <form wire:submit="save" class="stack">
                <x-admin.tr-field field="title" label="Title" :required="true" max="150" />
                <x-admin.tr-field field="location" label="Location" max="150" />
                <x-admin.tr-field field="description" label="Description" max="15000" type="rich" />
                <div class="row row-3">
                    <label>Category *
                        <x-ui.select model="category" :selected="$category" :options="array_combine($categories, $categories)" />
                        @error('category') <span class="err">{{ $message }}</span> @enderror
                    </label>
                    <label>Year<input wire:model="year" maxlength="4" inputmode="numeric" placeholder="2026">@error('year') <span class="err">{{ $message }}</span> @enderror</label>
                    <label>Order<input type="number" wire:model="sort" min="0" max="999"></label>
                </div>
                <div class="row row-4">
                    <label>Progress
                        <x-ui.select model="status" :selected="$status" :options="\App\Models\Project::STATUSES" />
                    </label>
                    <label>Storeys<input type="number" wire:model="floors" min="1" max="60" placeholder="e.g. 6">@error('floors') <span class="err">{{ $message }}</span> @enderror</label>
                    <label>Land (katha)<input wire:model="land_katha" inputmode="decimal" placeholder="e.g. 5">@error('land_katha') <span class="err">{{ $message }}</span> @enderror</label>
                    <label>Built area (sq ft)<input type="number" wire:model="floor_area_sqft" min="100" placeholder="e.g. 9600">@error('floor_area_sqft') <span class="err">{{ $message }}</span> @enderror</label>
                </div>
                <label>URL slug <small class="muted">(auto from English title)</small><input wire:model="slug" maxlength="150">@error('slug') <span class="err">{{ $message }}</span> @enderror</label>

                <x-admin.image-upload model="cover" :label="'Cover photo'.($editingId ? '' : ' *')" size="1600 × 1200 px · landscape (4:3)" :upload="$cover" :current="$current?->coverUrl()" :wide="true" />

                <x-admin.image-upload model="photos" label="Gallery photos (up to 12)" size="1600 × 1200 px · landscape (4:3)" :upload="$photos" :multiple="true" hint="JPG, PNG or WebP · max 5 MB each · resized, converted to WebP and stripped of location data automatically">
                    @foreach ($current?->galleryUrls() ?? [] as $url)
                        <figure class="up-tile" wire:key="gal-{{ $loop->index }}">
                            <img src="{{ $url }}" alt="">
                            <span class="up-tag"><x-icon name="check" /> Saved</span>
                            <button type="button" class="up-x" wire:click="removePhoto({{ $loop->index }})" data-confirm="Remove this photo?" aria-label="Remove this photo" title="Remove"><x-icon name="close" /></button>
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
                    </button> <button type="button" class="btn btn-ghost" wire:click="cancel">Cancel</button>
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
                                <button type="button" class="act-btn" wire:click="edit({{ $project->id }})" title="Edit" aria-label="Edit"><x-icon name="edit" /></button>
                                <button type="button" class="act-btn act-danger" wire:click="delete({{ $project->id }})" data-confirm="Delete this project and its photos?" title="Delete" aria-label="Delete"><x-icon name="trash" /></button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            {{ $projects->links('livewire.admin.partials.pagination') }}
        @endif
    </section>
</div>
