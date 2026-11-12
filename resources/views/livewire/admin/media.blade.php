<div class="stack-lg">
    <div class="stat-row">
        <div class="stat"><small>All photos</small><b>{{ $totalCount }}</b><span>{{ \Illuminate\Support\Number::fileSize($totalSize, 1) }}</span></div>
        <div class="stat"><small>Used on the website</small><b>{{ $totalCount - $unusedCount }}</b><span>Protected — cannot be deleted</span></div>
        <div class="stat stat-warn"><small>Not used anywhere</small><b>{{ $unusedCount }}</b><span>{{ \Illuminate\Support\Number::fileSize($unusedSize, 1) }} can be freed</span></div>
    </div>

    <section class="card">
        <div class="card-head">
            <div class="seg-tabs" role="tablist" aria-label="Filter photos">
                @foreach (['all' => 'All', 'used' => 'In use', 'unused' => 'Unused'] as $value => $text)
                    <button type="button" role="tab" @class(['is-active' => $show === $value]) aria-selected="{{ $show === $value ? 'true' : 'false' }}" wire:click="$set('show', '{{ $value }}')">{{ $text }}</button>
                @endforeach
            </div>
            <div class="row-actions">
                <x-ui.select model="folder" :live="true" :small="true" label="Folder" placeholder="All folders" :selected="$folder" :options="$folders" />
                @if ($unusedCount)
                    <button type="button" class="btn btn-danger btn-sm" wire:click="deleteAllUnused" data-confirm="Delete all {{ $unusedCount }} unused photos? Photos used on the website are never touched.">Delete all unused</button>
                @endif
            </div>
        </div>

        @error('media') <p class="alert alert-error">{{ $message }}</p> @enderror

        @if ($files->isEmpty())
            <div class="empty-state">
                <x-icon name="image" />
                <p>{{ $show === 'unused' ? 'No unused photos — nothing to clean up.' : 'No photos here yet.' }}</p>
            </div>
        @else
            <div class="media-grid">
                @foreach ($files as $file)
                    <figure @class(['media-item', 'is-unused' => $file['usedBy'] === []]) wire:key="m-{{ md5($file['path']) }}">
                        <a href="{{ $file['url'] }}" target="_blank" rel="noopener" class="media-thumb" title="Open full size">
                            <img src="{{ $file['url'] }}" alt="" loading="lazy">
                        </a>
                        @if ($file['usedBy'] === [])
                            <button type="button" class="up-x" wire:click="delete(@js($file['path']))" data-confirm="Delete this unused photo?" aria-label="Delete photo" title="Delete"><x-icon name="close" /></button>
                            <span class="up-tag up-tag-warn">Unused</span>
                        @else
                            <span class="up-tag"><x-icon name="check" /> In use</span>
                        @endif
                        <figcaption>
                            @forelse ($file['usedBy'] as $place)
                                <span class="media-use">{{ $place }}</span>
                            @empty
                                <span class="media-use muted">Not used on any page</span>
                            @endforelse
                            <small class="muted">{{ $folders[$file['folder']] }} · {{ \Illuminate\Support\Number::fileSize($file['size'], 1) }} · {{ \Illuminate\Support\Carbon::createFromTimestamp($file['modified'])->format('d M Y') }}</small>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        @endif
    </section>
</div>
