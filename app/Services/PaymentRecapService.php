<?php

namespace App\Services;

use App\Models\DailySale;
use App\Models\Location;
use App\Models\Taking;
use App\Models\TakingPaymentDetail;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pendapatan per metode pembayaran, dikelompokkan per rekapan (daily sale).
 * Data diambil dari taking_payment_details seluruh taking milik rekapan tersebut,
 * sama seperti halaman detail rekapan.
 */
class PaymentRecapService
{
    private const AMOUNT_FIELDS = [
        'sales_count',
        'sales_amount',
        'refund_count',
        'refund_amount',
        'recorded_amount',
        'counted_amount',
        'difference_amount',
    ];

    public function getLocationOptions(): Collection
    {
        return Location::where('entity_id', auth()->user()?->entity?->id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Location $location) => [
                'id' => $location->id,
                'name' => Str::title(Str::lower($location->name)),
            ]);
    }

    /**
     * Seluruh rekapan dalam filter (terbaru dulu), masing-masing dengan total dan
     * rincian per metode pembayaran. Volumenya kecil (± 2 rekapan per toko per hari),
     * jadi diolah sekaligus lalu dipakai untuk tabel, ringkasan, dan PDF.
     */
    public function getRecaps(): Collection
    {
        $recaps = $this->recapQuery()
            ->orderByDesc('local_sales_at')
            ->orderBy('location_name')
            ->orderByDesc('shift_number')
            ->get();

        $takingIds = $this->takingIdsByRecap($recaps);
        $allTakingIds = $takingIds->flatten()->unique()->all();
        $details = $this->paymentDetails($allTakingIds);
        $takings = Taking::whereIn('id', $allTakingIds)
            ->get(['id', 'sales_count'])
            ->keyBy('id');

        return $recaps->map(function (DailySale $recap) use ($takingIds, $details, $takings) {
            $ids = $takingIds[$recap->id] ?? [];
            $payments = $this->sumByPaymentMethod($details->whereIn('taking_id', $ids));

            return [
                'id' => $recap->id,
                'date' => $recap->local_sales_at,
                'location' => Str::title(Str::lower((string) $recap->location_name)),
                'shift' => 'Shift '.$recap->shift_number,
                'shift_number' => (int) $recap->shift_number,
                'is_closed' => $recap->taking_id !== null,
                'employee' => $this->employeeName($recap),
                'transaction_count' => (int) $takings->only($ids)->sum('sales_count'),
                ...$this->totals($payments),
                'payments' => $payments,
            ];
        });
    }

    /**
     * Baris tabel: satu baris per metode pembayaran per rekapan.
     */
    public function paginateRows(Collection $recaps): LengthAwarePaginator
    {
        $rows = $recaps->flatMap(fn (array $recap) => array_map(fn (array $payment) => [
            'recap_id' => $recap['id'],
            'date' => $recap['date'],
            'location' => $recap['location'],
            'shift' => $recap['shift'],
            'is_closed' => $recap['is_closed'],
            'employee' => $recap['employee'],
            ...$payment,
        ], $recap['payments']));

        $perPage = max(1, (int) request('per_page', 10));
        $page = Paginator::resolveCurrentPage();

        return (new Paginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => request()->url()],
        ))->withQueryString();
    }

    /**
     * Total per metode pembayaran untuk seluruh rekapan dalam filter.
     */
    public function summarize(Collection $recaps): array
    {
        $payments = $recaps->pluck('payments')
            ->flatten(1)
            ->groupBy('payment_method')
            ->map(fn (Collection $rows, string $method) => [
                'payment_method' => $method,
                ...$this->totals($rows->all()),
            ])
            ->sortByDesc('recorded_amount')
            ->values()
            ->all();

        return [
            'payments' => $payments,
            'totals' => $this->totals($payments),
            'recap_count' => $recaps->count(),
            'transaction_count' => $recaps->sum('transaction_count'),
        ];
    }

    /**
     * Rekapan dalam filter + nomor shift: urutan rekapan di lokasi & hari yang sama.
     * Filter tanggal selalu per hari penuh, jadi penomoran tidak terpengaruh filter.
     */
    private function recapQuery(): Builder
    {
        $startAt = request('start_at')
            ? Carbon::parse(request('start_at'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $endAt = request('end_at')
            ? Carbon::parse(request('end_at'))->endOfDay()
            : Carbon::now()->endOfDay();

        $recaps = DailySale::query()
            ->join('locations', 'locations.id', '=', 'daily_sales.location_id')
            ->where('daily_sales.entity_id', auth()->user()?->entity?->id)
            ->whereIn('daily_sales.location_id', $this->locationIds())
            ->whereBetween('daily_sales.local_sales_at', [$startAt, $endAt])
            ->select([
                'daily_sales.id',
                'daily_sales.location_id',
                'daily_sales.taking_id',
                'daily_sales.checkpoint_device_id',
                'daily_sales.local_sales_at',
                'daily_sales.employee_first_name',
                'daily_sales.employee_last_name',
                'locations.name as location_name',
            ])
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY daily_sales.location_id, DATE(daily_sales.local_sales_at) ORDER BY daily_sales.local_sales_at, daily_sales.id) as shift_number');

        return DailySale::query()->fromSub($recaps, 'daily_sales');
    }

    /**
     * Lokasi milik entity yang lolos filter select_all_location / locs / exclude_locs.
     *
     * @return array<int, int>
     */
    private function locationIds(): array
    {
        $ids = auth()->user()->entity->locations()->pluck('id')->all();

        $selectAll = request('select_all_location', '1') == '1';
        $locs = array_map('intval', (array) request('locs', []));
        $excludeLocs = array_map('intval', (array) request('exclude_locs', []));

        if ($selectAll && count($excludeLocs) > 0) {
            return array_values(array_diff($ids, $excludeLocs));
        }

        if (! $selectAll && count($locs) > 0) {
            return array_values(array_intersect($ids, $locs));
        }

        return $ids;
    }

    /**
     * Taking milik tiap rekapan (logika sama dengan DailySaleService::getTakings):
     * - sudah tutup: taking penutup + shift di bawahnya (parent_id).
     * - belum tutup: shift yang belum punya parent di device & lokasi yang sama.
     *
     * @return Collection<int, array<int, int>> [daily_sale_id => [taking_id, ...]]
     */
    private function takingIdsByRecap(Collection $recaps): Collection
    {
        $closingIds = $recaps->pluck('taking_id')->filter()->unique()->values();

        $children = $closingIds->isEmpty()
            ? collect()
            : Taking::whereIn('parent_id', $closingIds)
                ->where('is_shift', true)
                ->get(['id', 'parent_id'])
                ->groupBy('parent_id');

        $openRecaps = $recaps->whereNull('taking_id');
        $openShifts = $openRecaps->isEmpty()
            ? collect()
            : Taking::whereNull('parent_id')
                ->where('is_shift', true)
                ->whereIn('location_id', $openRecaps->pluck('location_id')->unique())
                ->whereIn('checkpoint_device_id', $openRecaps->pluck('checkpoint_device_id')->unique())
                ->get(['id', 'location_id', 'checkpoint_device_id']);

        return $recaps->mapWithKeys(function (DailySale $recap) use ($children, $openShifts) {
            if ($recap->taking_id) {
                $ids = [$recap->taking_id, ...($children[$recap->taking_id] ?? collect())->pluck('id')->all()];
            } else {
                $ids = $openShifts
                    ->where('location_id', $recap->location_id)
                    ->where('checkpoint_device_id', $recap->checkpoint_device_id)
                    ->pluck('id')
                    ->all();
            }

            return [$recap->id => $ids];
        });
    }

    /**
     * Rincian pembayaran per taking & metode (sudah dijumlah di database).
     *
     * @param  array<int, int>  $takingIds
     */
    private function paymentDetails(array $takingIds): Collection
    {
        if (! count($takingIds)) {
            return collect();
        }

        return TakingPaymentDetail::query()
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 'taking_payment_details.payment_method_id')
            ->whereIn('taking_payment_details.taking_id', $takingIds)
            ->groupBy('taking_payment_details.taking_id', 'taking_payment_details.payment_method_id', 'payment_methods.name')
            ->select([
                'taking_payment_details.taking_id',
                'taking_payment_details.payment_method_id',
                'payment_methods.name as payment_method_name',
            ])
            ->addSelect(array_map(
                fn (string $field) => DB::raw("SUM(taking_payment_details.{$field}) as {$field}"),
                self::AMOUNT_FIELDS
            ))
            ->toBase()
            ->get();
    }

    /**
     * Jumlahkan per metode pembayaran; metode yang semua nilainya 0 tidak ditampilkan.
     *
     * @return array<int, array<string, mixed>>
     */
    private function sumByPaymentMethod(Collection $details): array
    {
        return $details
            ->groupBy('payment_method_id')
            ->map(function (Collection $rows) {
                $row = ['payment_method' => $rows->first()->payment_method_name ?? '-'];

                foreach (self::AMOUNT_FIELDS as $field) {
                    $row[$field] = (int) $rows->sum($field);
                }

                return $row;
            })
            ->reject(fn (array $row) => collect(self::AMOUNT_FIELDS)->every(fn (string $field) => $row[$field] === 0))
            ->sortBy('payment_method')
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $payments
     * @return array<string, int>
     */
    private function totals(array $payments): array
    {
        $payments = collect($payments);

        return collect(self::AMOUNT_FIELDS)
            ->mapWithKeys(fn (string $field) => [$field => (int) $payments->sum($field)])
            ->all();
    }

    private function employeeName(DailySale $recap): string
    {
        $name = trim(Str::replace('_', ' ', $recap->employee_first_name.' '.$recap->employee_last_name));

        return $name !== '' ? Str::title($name) : '-';
    }
}
