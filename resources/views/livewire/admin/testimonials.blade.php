<div class="stack-lg">
    <p class="alert alert-info">Only add real reviews from real clients (for example, from your Facebook page reviews), with their permission. The section stays hidden on the website until at least one testimonial is visible.</p>

    @if ($showForm)
        <section class="card">
            <div class="card-head">
                <h2>{{ $editingId ? 'Edit testimonial' : 'New testimonial' }}</h2>
                <button type="button" class="close-btn" wire:click="cancel" title="Close" aria-label="Close form"><x-icon name="close" /></button>
            </div>
            <form wire:submit="save" class="stack">
                <div class="row row-3">
                    <label>Client name *<input wire:model="name" maxlength="100">@error('name') <span class="err">{{ $message }}</span> @enderror</label>
                    <div class="field"><span class="field-label">Rating</span>
                        <x-ui.select label="Rating" model="rating" :selected="$rating" :options="[5 => '★★★★★', 4 => '★★★★', 3 => '★★★', 2 => '★★', 1 => '★']" />
                    </div>
                    <label>Order<input type="number" wire:model="sort" min="0" max="999"></label>
                </div>
                <x-admin.tr-field field="subtitle" label="Location / project" max="120" />
                <x-admin.tr-field field="quote" label="Review" :required="true" max="1000" type="textarea" rows="3" />
                <x-admin.image-upload model="photo" label="Photo (optional)" size="400 × 400 px · square" :upload="$photo" :current="$current?->photoUrl()" :remove-saved="$current?->photoUrl() ? 'removePhoto' : null" />
                <label class="check"><input type="checkbox" wire:model="is_active"> Show on website</label>
                <div class="row-actions"><button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save,photo">Save testimonial</button> <button type="button" class="btn btn-ghost" wire:click="cancel">Cancel</button></div>
            </form>
        </section>
    @endif

    <section class="card">
        <div class="card-head">
            <h2>Testimonials</h2>
            <button type="button" class="btn btn-primary btn-sm" wire:click="create">+ Add testimonial</button>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Client</th><th>Review</th><th>Status</th><th class="right">Actions</th></tr></thead>
                <tbody>
                    @forelse ($testimonials as $testimonial)
                        <tr wire:key="t-{{ $testimonial->id }}">
                            <td><b>{{ $testimonial->name }}</b><br><span class="muted small">{{ str_repeat('★', $testimonial->rating) }}</span></td>
                            <td>{{ \Illuminate\Support\Str::limit($testimonial->getTranslation('quote', 'en'), 120) }}</td>
                            <td><span @class(['pill', 'pill-closed' => $testimonial->is_active, 'pill-spam' => ! $testimonial->is_active])>{{ $testimonial->is_active ? 'Visible' : 'Hidden' }}</span></td>
                            <td class="right nowrap">
                                <button type="button" class="act-btn" wire:click="edit({{ $testimonial->id }})" title="Edit" aria-label="Edit"><x-icon name="edit" /></button>
                                <button type="button" class="act-btn act-danger" wire:click="delete({{ $testimonial->id }})" data-confirm="Delete this testimonial?" title="Delete" aria-label="Delete"><x-icon name="trash" /></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="muted">No testimonials yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $testimonials->links('livewire.admin.partials.pagination') }}
    </section>
</div>
