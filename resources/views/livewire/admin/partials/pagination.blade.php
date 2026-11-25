@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $pages = collect([1, $current - 1, $current, $current + 1, $last])->filter(fn (int $page): bool => $page >= 1 && $page <= $last)->unique()->sort()->values();
        $pageName = $paginator->getPageName();
    @endphp
    <nav class="pager" aria-label="Pagination">
        <span class="pager-info">Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
        <div class="pager-pages">
            @if ($paginator->onFirstPage())
                <span class="pager-btn is-disabled" aria-hidden="true"><x-icon name="chevron" class="pager-prev" /></span>
            @else
                <button type="button" class="pager-btn" wire:click="previousPage('{{ $pageName }}')" aria-label="Previous page"><x-icon name="chevron" class="pager-prev" /></button>
            @endif

            @foreach ($pages as $page)
                @if (! $loop->first && $page - $pages[$loop->index - 1] > 1)
                    <span class="pager-gap">…</span>
                @endif
                @if ($page === $current)
                    <span class="pager-btn is-current" aria-current="page">{{ $page }}</span>
                @else
                    <button type="button" class="pager-btn" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')">{{ $page }}</button>
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <button type="button" class="pager-btn" wire:click="nextPage('{{ $pageName }}')" aria-label="Next page"><x-icon name="chevron" class="pager-next" /></button>
            @else
                <span class="pager-btn is-disabled" aria-hidden="true"><x-icon name="chevron" class="pager-next" /></span>
            @endif
        </div>
    </nav>
@endif
