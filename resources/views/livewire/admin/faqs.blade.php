<div class="stack-lg">
    @if ($showForm)
        <section class="card">
            <div class="card-head">
                <h2>{{ $editingId ? 'Edit FAQ' : 'New FAQ' }}</h2>
                <button type="button" class="link-btn" wire:click="cancel">Cancel ✕</button>
            </div>
            <form wire:submit="save" class="stack">
                <x-admin.tr-field field="question" label="Question" :required="true" max="255" />
                <x-admin.tr-field field="answer" label="Answer" :required="true" max="3000" type="textarea" rows="4" />
                <div class="row">
                    <label>Order<input type="number" wire:model="sort" min="0" max="999"></label>
                    <label class="check"><input type="checkbox" wire:model="is_active"> Show on website</label>
                </div>
                <div class="row-actions"><button type="submit" class="btn btn-primary">Save FAQ</button></div>
            </form>
        </section>
    @endif

    <section class="card">
        <div class="card-head">
            <h2>Frequently asked questions</h2>
            <button type="button" class="btn btn-primary btn-sm" wire:click="create">+ Add FAQ</button>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>#</th><th>Question</th><th>Status</th><th class="right">Actions</th></tr></thead>
                <tbody>
                    @forelse ($faqs as $faq)
                        <tr wire:key="faq-{{ $faq->id }}">
                            <td>{{ $faq->sort }}</td>
                            <td><b>{{ $faq->getTranslation('question', 'en') }}</b><br><span class="muted small">{{ $faq->getTranslation('question', 'bn', false) ?: '— no Bangla yet' }}</span></td>
                            <td><span @class(['pill', 'pill-closed' => $faq->is_active, 'pill-spam' => ! $faq->is_active])>{{ $faq->is_active ? 'Visible' : 'Hidden' }}</span></td>
                            <td class="right nowrap">
                                <button type="button" class="btn btn-ghost btn-sm" wire:click="edit({{ $faq->id }})">Edit</button>
                                <button type="button" class="btn btn-danger btn-sm" wire:click="delete({{ $faq->id }})" wire:confirm="Delete this FAQ?">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="muted">No FAQs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
