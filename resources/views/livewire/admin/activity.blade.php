<section class="card">
    <p class="muted">Every sign-in, failed attempt, lockout and important change is recorded here with the IP address.</p>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>When</th><th>Event</th><th>Details</th><th>User</th><th>IP</th></tr></thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr wire:key="log-{{ $log->id }}">
                        <td class="nowrap">{{ $log->created_at->timezone(config('fdh.display_timezone'))->format('d M Y, h:i:s A') }}</td>
                        <td><span @class(['pill', 'pill-spam' => str_contains($log->action, 'failed') || str_contains($log->action, 'locked'), 'pill-closed' => $log->action === 'login.success'])>{{ $log->action }}</span></td>
                        <td>{{ $log->description }}</td>
                        <td>{{ $log->user?->name ?? '—' }}</td>
                        <td class="muted">{{ $log->ip }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">No activity recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links('livewire.admin.partials.pagination') }}
</section>
