<div class="stack-lg">
    @if ($showForm)
        <section class="card">
            <div class="card-head">
                <h2>{{ $editingId ? 'Edit service' : 'New service' }}</h2>
                <button type="button" class="close-btn" wire:click="cancel" title="Close" aria-label="Close form"><x-icon name="close" /></button>
            </div>
            <form wire:submit="save" class="stack">
                <x-admin.tr-field field="title" label="Title" :required="true" max="120" />
                <x-admin.tr-field field="summary" label="Short summary" :required="true" max="300" type="textarea" rows="2" />
                <x-admin.tr-field field="description" label="Full description" max="15000" type="rich" />
                <x-admin.tr-field field="features" label="Features" hint="comma separated" max="500" />
                <div class="row row-3">
                    <label>URL slug <small class="muted">(auto from English title)</small><input wire:model="slug" maxlength="120">@error('slug') <span class="err">{{ $message }}</span> @enderror</label>
                    <label>Icon
                        <x-ui.select model="icon" :selected="$icon" :options="collect($icons)->mapWithKeys(fn ($name) => [$name => ucfirst($name)])->all()" />
                    </label>
                    <label>Order<input type="number" wire:model="sort" min="0" max="999"></label>
                </div>
                <label class="check"><input type="checkbox" wire:model="is_active"> Show on website</label>
                <div class="row-actions"><button type="submit" class="btn btn-primary">Save service</button> <button type="button" class="btn btn-ghost" wire:click="cancel">Cancel</button></div>
            </form>
        </section>
    @endif

    <section class="card">
        <div class="card-head">
            <h2>All services</h2>
            <button type="button" class="btn btn-primary btn-sm" wire:click="create">+ Add service</button>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>#</th><th>Service</th><th>Status</th><th class="right">Actions</th></tr></thead>
                <tbody>
                    @foreach ($services as $service)
                        <tr wire:key="svc-{{ $service->id }}">
                            <td>{{ $service->sort }}</td>
                            <td><b>{{ $service->getTranslation('title', 'en') }}</b> <span class="muted">· {{ $service->getTranslation('title', 'bn', false) ?: '— no Bangla yet' }}</span><br><span class="muted small">{{ $service->getTranslation('summary', 'en') }}</span></td>
                            <td><button type="button" wire:click="toggle({{ $service->id }})" @class(['pill', 'pill-closed' => $service->is_active, 'pill-spam' => ! $service->is_active])>{{ $service->is_active ? 'Visible' : 'Hidden' }}</button></td>
                            <td class="right nowrap">
                                <button type="button" class="act-btn" wire:click="edit({{ $service->id }})" title="Edit" aria-label="Edit"><x-icon name="edit" /></button>
                                <button type="button" class="act-btn act-danger" wire:click="delete({{ $service->id }})" data-confirm="Delete this service?" title="Delete" aria-label="Delete"><x-icon name="trash" /></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $services->links('livewire.admin.partials.pagination') }}
    </section>
</div>
