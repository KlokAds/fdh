<div class="grid-2">
    <section class="card" data-push
        data-public-key="{{ $publicKey }}"
        data-worker="{{ route('admin.push.worker') }}"
        data-subscribe="{{ route('admin.push.subscribe') }}"
        data-unsubscribe="{{ route('admin.push.unsubscribe') }}">
        <h2>Inquiry alerts on this device</h2>
        <p class="muted">Get a notification on your phone or computer the moment a visitor sends an inquiry. Free — no SMS gateway needed.</p>

        @if (! $configured)
            <p class="alert alert-error">Push keys are not set up on the server yet. Run <code>php artisan fdh:vapid</code> and add the two lines to <code>.env</code>.</p>
        @else
            <p class="badge" data-push-status>Checking this device…</p>
            <div class="row-actions" wire:ignore>
                <button type="button" class="btn btn-primary" data-push-enable hidden>Turn on notifications</button>
                <button type="button" class="btn btn-ghost" data-push-disable hidden>Turn off on this device</button>
            </div>
            <div class="row-actions">
                <button type="button" class="btn btn-ghost btn-sm" wire:click="sendTest" wire:loading.attr="disabled">Send a test notification</button>
            </div>
            @if ($result)
                <p class="alert alert-info" role="status">{{ $result }}</p>
            @endif
        @endif
    </section>

    <section class="card">
        <h2>How to set it up on a phone</h2>
        <ol class="steps">
            <li><b>Android (Chrome):</b> open this admin panel, sign in, then tap <b>Turn on notifications</b> and allow.</li>
            <li><b>iPhone (iOS 16.4+):</b> open the panel in Safari → Share → <b>Add to Home Screen</b>. Open it from the home-screen icon, sign in, then tap <b>Turn on notifications</b>.</li>
            <li>Tap <b>Send a test notification</b> to check it works.</li>
            <li>Notifications only work on the live site (HTTPS) or on this computer while testing.</li>
        </ol>

        <h2>Your devices</h2>
        @if ($devices->isEmpty())
            <p class="muted">No devices yet.</p>
        @else
            <ul class="device-list">
                @foreach ($devices as $device)
                    <li wire:key="dev-{{ $device->id }}">
                        <span>
                            <b>{{ \Illuminate\Support\Str::limit($device->device ?: 'Unknown browser', 60) }}</b>
                            <small class="muted">Added {{ $device->created_at->diffForHumans() }}{{ $device->last_used_at ? ' · last alert '.$device->last_used_at->diffForHumans() : '' }}</small>
                        </span>
                        <button type="button" class="btn btn-danger btn-sm" wire:click="removeDevice({{ $device->id }})" wire:confirm="Stop notifications on this device?">Remove</button>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
