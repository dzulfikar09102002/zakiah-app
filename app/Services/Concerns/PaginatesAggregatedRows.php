<?php

namespace App\Services\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Paginasi untuk query agregat (GROUP BY) yang hasil grupnya sedikit.
 *
 * paginate() pada query ber-GROUP BY menjalankan agregasi dua kali: sekali di dalam
 * "select count(*) from (...)" untuk total, sekali lagi untuk data halaman. Di tabel besar
 * (mis. sale_transaction_details) itu berarti dua kali full scan. Di sini agregasi dijalankan
 * sekali, lalu hasilnya dipotong per halaman di PHP. Bentuk keluaran sama dengan
 * ->paginate()->through()->withQueryString().
 */
trait PaginatesAggregatedRows
{
    /**
     * @param  callable|null  $transform  Ubah tiap baris (null = baris apa adanya).
     * @param  bool  $asModels  true = tetap model Eloquent (casts/appends ikut), false = baris ringan.
     */
    protected function paginateAggregated(Builder $query, ?callable $transform = null, bool $asModels = false): LengthAwarePaginator
    {
        $perPage = (int) request('per_page', 10);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $rows = $asModels ? $query->get() : $query->toBase()->get();
        $items = $rows->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $transform ? $items->map($transform) : $items,
            $rows->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ],
        );
    }
}
