<div class="stack-lg">
    @if ($showForm)
        <section class="card">
            <div class="card-head">
                <h2>{{ $editingId ? 'Edit team member' : 'New team member' }}</h2>
                <button type="button" class="close-btn" wire:click="cancel" title="Close" aria-label="Close form"><x-icon name="close" /></button>
            </div>
            <form wire:submit="save" class="stack">
                <x-admin.tr-field field="name" label="Name" :required="true" max="100" />
                <x-admin.tr-field field="designation" label="Designation" :required="true" max="120" />
                <x-admin.tr-field field="bio" label="Short bio" max="1000" type="textarea" rows="3" />
                <div class="row">
                    <label>Phone<input wire:model="phone" maxlength="30">@error('phone') <span class="err">{{ $message }}</span> @enderror</label>
                    <label>Email<input type="email" wire:model="email" maxlength="150">@error('email') <span class="err">{{ $message }}</span> @enderror</label>
                </div>
                <div class="row">
                    <label>Facebook URL<input type="url" wire:model="facebook" maxlength="255">@error('facebook') <span class="err">{{ $message }}</span> @enderror</label>
                    <label>LinkedIn URL<input type="url" wire:model="linkedin" maxlength="255">@error('linkedin') <span class="err">{{ $message }}</span> @enderror</label>
                </div>
                <div class="row">
                    <x-admin.image-upload model="photo" label="Photo" size="800 × 1000 px · portrait (4:5)" :upload="$photo" :current="$current?->photoUrl()" :remove-saved="$current?->photoUrl() ? 'removePhoto' : null" />
                    <label>Order<input type="number" wire:model="sort" min="0" max="999"></label>
                </div>
                <label class="check"><input type="checkbox" wire:model="is_active"> Show on website</label>
                <div class="row-actions"><button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save,photo">Save team member</button> <button type="button" class="btn btn-ghost" wire:click="cancel">Cancel</button></div>
            </form>
        </section>
    @endif

    <section class="card">
        <div class="card-head">
            <h2>Team members</h2>
            <button type="button" class="btn btn-primary btn-sm" wire:click="create">+ Add member</button>
        </div>
        @if ($members->isEmpty())
            <p class="muted">No team members yet.</p>
        @else
            <div class="people">
                @foreach ($members as $member)
                    <article class="person" wire:key="tm-{{ $member->id }}">
                        @if ($member->photoUrl())
                            <img src="{{ $member->photoUrl() }}" alt="" class="avatar">
                        @else
                            <span class="avatar avatar-text">{{ $member->initials() }}</span>
                        @endif
                        <div>
                            <b>{{ $member->getTranslation('name', 'en') }}</b>
                            <span class="muted small">{{ $member->getTranslation('designation', 'en') }}{{ $member->is_active ? '' : ' · Hidden' }}</span>
                        </div>
                        <div class="row-actions">
                            <button type="button" class="act-btn" wire:click="edit({{ $member->id }})" title="Edit" aria-label="Edit"><x-icon name="edit" /></button>
                            <button type="button" class="act-btn act-danger" wire:click="delete({{ $member->id }})" data-confirm="Delete this team member?" title="Delete" aria-label="Delete"><x-icon name="trash" /></button>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
        {{ $members->links('livewire.admin.partials.pagination') }}
    </section>
</div>
