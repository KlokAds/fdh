<div class="stack-lg">
    <div class="toolbar">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Search name, phone, location…" maxlength="100">
        <x-ui.select model="status" :live="true" label="Filter by status" placeholder="All statuses" :selected="$status" :options="['unread' => 'Unread', 'mine' => 'Assigned to me'] + \App\Models\Inquiry::STATUSES" />
    </div>

    <div @class(['split', 'has-detail' => $current])>
        <section class="card">
            @if ($inquiries->isEmpty())
                <p class="muted">No inquiries found.</p>
            @else
                <div class="table-wrap">
                    <table class="table table-click">
                        <thead><tr><th>Name</th><th>Phone</th><th>Service</th><th>Status</th><th>Assigned</th><th>Received</th></tr></thead>
                        <tbody>
                            @foreach ($inquiries as $inquiry)
                                <tr wire:key="inq-{{ $inquiry->id }}" wire:click="open({{ $inquiry->id }})" @class(['is-selected' => $current?->id === $inquiry->id, 'is-unread' => $inquiry->read_at === null])>
                                    <td>@if ($inquiry->read_at === null)<span class="unread-dot" title="Unread"></span>@endif<b>{{ $inquiry->name }}</b> @if ($inquiry->locale === 'bn')<span class="pill">বাং</span>@endif</td>
                                    <td>{{ $inquiry->phone }}</td>
                                    <td>{{ $inquiry->service ?: '—' }}</td>
                                    <td><span class="pill pill-{{ $inquiry->status }}">{{ \App\Models\Inquiry::STATUSES[$inquiry->status] ?? $inquiry->status }}</span></td>
                                    <td class="small">{{ $inquiry->assignee?->name ?? '—' }}</td>
                                    <td class="nowrap">{{ $inquiry->created_at->format('d M Y, h:i A') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $inquiries->links('livewire.admin.partials.pagination') }}
            @endif
        </section>

        @if ($current)
            <section class="card detail-card">
                <div class="card-head">
                    <h2>{{ $current->name }}</h2>
                    <button type="button" class="close-btn" wire:click="close" title="Close" aria-label="Close"><x-icon name="close" /></button>
                </div>
                <dl class="facts">
                    <dt>Phone</dt><dd><a href="tel:{{ $current->phone }}">{{ $current->phone }}</a> · <a href="https://wa.me/{{ preg_replace('/^0/', '880', preg_replace('/\D/', '', $current->phone)) }}" target="_blank" rel="noopener noreferrer">WhatsApp ↗</a></dd>
                    @if ($current->email)<dt>Email</dt><dd><a href="mailto:{{ $current->email }}">{{ $current->email }}</a></dd>@endif
                    <dt>Service</dt><dd>{{ $current->service ?: '—' }}</dd>
                    <dt>Location</dt><dd>{{ $current->location ?: '—' }}</dd>
                    <dt>Language</dt><dd>{{ config('fdh.locales.'.$current->locale.'.name', $current->locale) }}</dd>
                    <dt>Received</dt><dd>{{ $current->created_at->format('d M Y, h:i A') }}</dd>
                    <dt>Read</dt><dd>{{ $current->read_at ? $current->read_at->diffForHumans().' · via '.(\App\Models\Inquiry::READ_VIA[$current->read_via] ?? $current->read_via) : 'Not yet' }}</dd>
                    <dt>Email alert</dt><dd>{{ $current->emailed_at ? 'Sent '.$current->emailed_at->diffForHumans() : 'Not sent' }}</dd>
                    <dt>IP</dt><dd class="muted">{{ $current->ip }}</dd>
                </dl>
                @if ($current->message)
                    <div class="message-box">{{ $current->message }}</div>
                @endif

                @can('inquiries.manage')
                    <div class="status-row">
                        @foreach (\App\Models\Inquiry::STATUSES as $value => $label)
                            <button type="button" wire:key="st-{{ $value }}" wire:click="setStatus('{{ $value }}')" @class(['chip-btn', 'is-active' => $current->status === $value])>{{ $label }}</button>
                        @endforeach
                    </div>
                    <form wire:submit="saveNote" class="stack">
                        <label>Assigned to
                            <x-ui.select model="assignee" placeholder="Nobody" :selected="$assignee" :options="$staff->pluck('name', 'id')->all()" />
                            @error('assignee') <span class="err">{{ $message }}</span> @enderror
                        </label>
                        <label>Internal note
                            <textarea wire:model="note" rows="3" maxlength="2000" placeholder="Call notes, follow-up date…"></textarea>
                        </label>
                        @error('note') <span class="err">{{ $message }}</span> @enderror
                        <div class="row-actions">
                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                            @can('inquiries.delete')
                                <button type="button" class="btn btn-danger btn-sm" wire:click="delete" data-confirm="Delete this inquiry permanently?">Delete</button>
                            @endcan
                        </div>
                    </form>
                @else
                    <p class="muted small">Status: {{ \App\Models\Inquiry::STATUSES[$current->status] ?? $current->status }} · Assigned: {{ $current->assignee?->name ?? 'nobody' }}</p>
                    @if ($current->note)<div class="message-box">{{ $current->note }}</div>@endif
                @endcan
            </section>
        @endif
    </div>
</div>
