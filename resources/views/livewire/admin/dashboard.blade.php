<div class="stack-lg">
    <section class="welcome">
        <div>
            <h2>Welcome back, {{ auth()->user()->name }} 👋</h2>
            <p class="muted">Signed in as <b>{{ auth()->user()->roles->pluck('name')->implode(', ') }}</b>.</p>
        </div>
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">View website ↗</a>
    </section>

    @if ($hasSamples)
        <section class="card sample-banner">
            <div>
                <h2>Sample photos and projects are showing on the website</h2>
                <p class="muted">They make the site look complete until your own content is ready. Upload real photos (Content &amp; settings, Projects) — each upload replaces its sample — or remove them all at once before launch.</p>
            </div>
            @can('settings.manage')
                <button type="button" class="btn btn-danger btn-sm" wire:click="removeSamples" data-confirm="Remove all sample photos and sample projects from the website?">Remove all samples</button>
            @endcan
        </section>
    @endif

    <div class="stats">
        @if ($canSeeInquiries)
            <a href="{{ route('admin.inquiries', ['status' => 'unread']) }}" wire:navigate class="stat stat-accent"><b>{{ $newInquiries }}</b><span>Unread inquiries</span></a>
            <a href="{{ route('admin.inquiries', ['status' => 'mine']) }}" wire:navigate class="stat"><b>{{ $myInquiries }}</b><span>Assigned to me (open)</span></a>
            <a href="{{ route('admin.inquiries') }}" wire:navigate class="stat"><b>{{ $totalInquiries }}</b><span>Total inquiries</span></a>
        @endif
        @foreach ([['projects', $projects, 'Projects', 'projects.manage'], ['services', $services, 'Active services', 'services.manage'], ['team', $team, 'Team page profiles', 'team.manage']] as [$page, $count, $label, $ability])
            @can($ability)
                <a href="{{ route('admin.'.$page) }}" wire:navigate class="stat"><b>{{ $count }}</b><span>{{ $label }}</span></a>
            @else
                <div class="stat"><b>{{ $count }}</b><span>{{ $label }}</span></div>
            @endcan
        @endforeach
        @if ($failedLogins !== null)
            <a href="{{ route('admin.activity') }}" wire:navigate @class(['stat', 'stat-danger' => $failedLogins > 0])><b>{{ $failedLogins }}</b><span>Failed sign-ins (24h)</span></a>
        @endif
    </div>

    @if ($canSeeInquiries)
        <section class="card">
            <div class="card-head">
                <h2>Latest inquiries</h2>
                <a href="{{ route('admin.inquiries') }}" wire:navigate class="btn btn-ghost btn-sm">View all</a>
            </div>
            @if ($latest->isEmpty())
                <p class="muted">No inquiries yet. They will appear here when visitors use the contact form.</p>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Name</th><th>Phone</th><th>Service</th><th>Status</th><th>Received</th></tr></thead>
                        <tbody>
                            @foreach ($latest as $inquiry)
                                <tr wire:key="inq-{{ $inquiry->id }}">
                                    <td>{{ $inquiry->name }}</td>
                                    <td><a href="tel:{{ $inquiry->phone }}">{{ $inquiry->phone }}</a></td>
                                    <td>{{ $inquiry->service ?: '—' }}</td>
                                    <td><span class="pill pill-{{ $inquiry->status }}">{{ \App\Models\Inquiry::STATUSES[$inquiry->status] ?? $inquiry->status }}</span></td>
                                    <td>{{ $inquiry->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif

    <section class="card">
        <h2>Quick actions</h2>
        <div class="quick">
            @can('projects.manage')<a href="{{ route('admin.projects') }}" wire:navigate class="quick-item">＋ Add a project</a>@endcan
            @can('content.manage')<a href="{{ route('admin.content', ['tab' => 'hero']) }}" wire:navigate class="quick-item">✎ Edit home page text</a>@endcan
            @can('team.manage')<a href="{{ route('admin.team') }}" wire:navigate class="quick-item">👥 Manage team</a>@endcan
            @can('settings.manage')<a href="{{ route('admin.content', ['tab' => 'branding']) }}" wire:navigate class="quick-item">🎨 Logo, colours &amp; theme</a>@endcan
            @can('users.manage')<a href="{{ route('admin.users') }}" wire:navigate class="quick-item">🔐 Admin users &amp; roles</a>@endcan
            <a href="{{ route('admin.security') }}" wire:navigate class="quick-item">🛡 My account security</a>
        </div>
    </section>
</div>
