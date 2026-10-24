<div class="tb-dd" data-dropdown @if ($canView) wire:poll.60s @endif>
    <button type="button" class="tb-btn" data-dropdown-toggle aria-expanded="false" aria-label="Notifications{{ $unreadCount ? " ({$unreadCount} unread)" : '' }}">
        <x-icon name="bell" />
        @if ($unreadCount)
            <span class="tb-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </button>
    <div class="tb-panel tb-panel-wide">
        <div class="tb-panel-head">
            <b>Notifications</b>
            @if ($unreadCount)<span class="pill pill-new">{{ $unreadCount }} new</span>@endif
        </div>
        @if (! $canView)
            <p class="tb-empty">You have no notifications.</p>
        @elseif ($latest->isEmpty())
            <p class="tb-empty"><x-icon name="check" /> All caught up — no unread inquiries.</p>
        @else
            <ul class="tb-list">
                @foreach ($latest as $inquiry)
                    <li wire:key="tb-inq-{{ $inquiry->id }}">
                        <a href="{{ route('admin.inquiries', ['open' => $inquiry->id]) }}" wire:navigate>
                            <span class="tb-dot"></span>
                            <span class="tb-item">
                                <b>{{ $inquiry->name }}</b>
                                <small>{{ $inquiry->service ?: 'General inquiry' }} · {{ $inquiry->created_at->diffForHumans() }}</small>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
        @if ($canView)
            <div class="tb-panel-foot">
                <a href="{{ route('admin.inquiries') }}" wire:navigate>View all inquiries →</a>
                <a href="{{ route('admin.notifications') }}" wire:navigate>Alert settings</a>
            </div>
        @endif
    </div>
</div>
