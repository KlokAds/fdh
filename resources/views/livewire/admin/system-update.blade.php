<div class="grid-2">
    <section class="card">
        <h2>Website version</h2>

        @if ($sourceCheckout)
            <p class="alert alert-info">This is a development copy (source code with git). It is updated with git, not from here. On the live site this page installs new versions from GitHub in one click.</p>
        @endif

        <dl class="facts">
            <dt>Installed</dt>
            <dd>{{ $current ? substr($current['sha'], 0, 7).' — '.$current['subject'] : 'Unknown (installed by hand)' }}</dd>
            @if ($current && $current['built_at'])
                <dt>Built</dt><dd>{{ \Illuminate\Support\Carbon::parse($current['built_at'])->timezone('Asia/Dhaka')->format('d M Y, h:i A') }}</dd>
            @endif
            @unless ($sourceCheckout)
                <dt>Newest on GitHub</dt>
                <dd>{{ $latest ? substr($latest['sha'], 0, 7).' — '.$latest['subject'] : 'Could not reach GitHub' }}</dd>
            @endunless
            <dt>Source</dt><dd class="muted">github.com/{{ $repo }} ({{ $branch }})</dd>
        </dl>

        @unless ($sourceCheckout)
            @if ($behind)
                <p class="badge badge-warn">An update is available</p>
            @elseif ($latest)
                <p class="badge badge-ok">✓ Up to date</p>
            @endif

            <div class="row-actions">
                @if ($behind)
                    <button type="button" class="btn btn-primary" wire:click="install" wire:loading.attr="disabled"
                        data-confirm="Install the update now? The website shows a short maintenance notice for about a minute.">
                        <span wire:loading.remove wire:target="install">Update now</span>
                        <span wire:loading wire:target="install">Updating… keep this page open</span>
                    </button>
                @endif
                <button type="button" class="btn btn-ghost" wire:click="check" wire:loading.attr="disabled">Check again</button>
            </div>
        @endunless

        @if ($error)
            <p class="alert alert-error" role="alert">Update failed: {{ $error }}</p>
        @endif
        @if ($log)
            <div class="alert alert-success" role="status">
                <b>Update finished.</b>
                <ul class="plain-list">
                    @foreach ($log as $line)
                        <li wire:key="log-{{ $loop->index }}">{{ $line }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>

    <section class="card">
        <h2>What's new</h2>
        @if ($changes)
            <ul class="plain-list">
                @foreach ($changes as $change)
                    <li wire:key="chg-{{ $loop->index }}">{{ $change }}</li>
                @endforeach
            </ul>
        @else
            <p class="muted">No pending changes.</p>
        @endif

        <h2>How updates work</h2>
        <ol class="steps">
            <li>Code changes are pushed to GitHub. GitHub tests them and builds a ready-to-run version automatically.</li>
            <li>This page shows when that version is newer than the website, with the list of changes.</li>
            <li><b>Update now</b> installs it: new files, database changes and cache refresh. Your settings (.env), uploaded photos and inquiries are never touched.</li>
        </ol>
    </section>
</div>
