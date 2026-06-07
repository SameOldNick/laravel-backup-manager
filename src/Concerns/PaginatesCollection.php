<?php

namespace SameOldNick\BackupManager\Concerns;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

trait PaginatesCollection
{
    /**
     * Paginate a collection of items.
     *
     * @param  int  $perPage  Number of items per page
     * @return LengthAwarePaginator Paginated collection
     */
    public function paginate(int $perPage = 15, ?int $page = null): LengthAwarePaginator
    {
        /** @var Collection $this */
        $page = $page ?? request()->query('page', 1);

        $total = $this->count();
        $items = $this->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
