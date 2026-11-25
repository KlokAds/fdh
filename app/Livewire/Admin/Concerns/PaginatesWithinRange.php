<?php

namespace App\Livewire\Admin\Concerns;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Pagination that never lands on an empty page: after deleting the last item of the last
 * page, the list moves back to the new last page instead of showing "nothing here".
 */
trait PaginatesWithinRange
{
    protected function paginateWithinRange(Builder $query, int $perPage): LengthAwarePaginator
    {
        $page = (clone $query)->paginate($perPage);

        if ($page->isEmpty() && $page->currentPage() > 1) {
            $this->setPage($page->lastPage());
            $page = $query->paginate($perPage, page: $page->lastPage());
        }

        return $page;
    }
}
