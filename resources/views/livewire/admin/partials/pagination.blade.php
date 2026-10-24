@if ($paginator->hasPages())
    <nav class="pager" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="pager-btn is-disabled">← Previous</span>
        @else
            <button type="button" class="pager-btn" wire:click="previousPage('{{ $paginator->getPageName() }}')">← Previous</button>
        @endif
        <span class="pager-info">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())
            <button type="button" class="pager-btn" wire:click="nextPage('{{ $paginator->getPageName() }}')">Next →</button>
        @else
            <span class="pager-btn is-disabled">Next →</span>
        @endif
    </nav>
@endif
