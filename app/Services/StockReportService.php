<?php

namespace App\Services;

use App\Models\Location;
use App\Models\ProductStockMovement;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;

class StockReportService
{
    public function getLocationOptions(): Collection
    {
        return Location::query()
            ->where('entity_id', auth()->user()?->entity?->id)
            ->orderBy('name')
            ->get()
            ->map(fn (Location $location) => [
                'label' => Str::title(Str::lower($location->name)),
                'value' => $location->id,
            ]);
    }

    /**
     * Kartu stok: total stok masuk & keluar per produk di satu lokasi.
     * Mengembalikan null bila lokasi belum dipilih.
     */
    public function getStockCards(): ?LengthAwarePaginator
    {
        $locationId = (int) request('loc');

        if ($locationId <= 0) {
            return null;
        }

        return $this->stockCardQuery($locationId)
            ->paginate(request('per_page', 10))
            ->through(fn ($row) => $this->transformStockCard($row))
            ->withQueryString();
    }

    public function getStockCardsForPdf(int $locationId): LazyCollection
    {
        return $this->stockCardQuery($locationId)
            ->limit(ReportPdfService::MAX_ROWS + 1)
            ->cursor()
            ->map(fn ($row) => $this->transformStockCard($row));
    }

    /**
     * Pergerakan stok: stok masuk & keluar per produk, lokasi, dan tanggal.
     */
    public function getStockMovements(): LengthAwarePaginator
    {
        return $this->stockMovementQuery()
            ->paginate(request('per_page', 10))
            ->through(fn ($row) => $this->transformStockMovement($row))
            ->withQueryString();
    }

    public function getStockMovementsForPdf(): LazyCollection
    {
        return $this->stockMovementQuery()
            ->limit(ReportPdfService::MAX_ROWS + 1)
            ->cursor()
            ->map(fn ($row) => $this->transformStockMovement($row));
    }

    private function stockCardQuery(int $locationId): Builder
    {
        return $this->baseQuery()
            ->where('product_stock_movements.location_id', $locationId)
            ->select([
                'products.id as product_id',
                'products.name as product_name',
                'products.sku as product_sku',
                'products.sell_price',
                'product_units.name as product_unit_name',
            ])
            ->selectRaw('SUM(product_stock_movements.stock_in) as stock_in')
            ->selectRaw('SUM(product_stock_movements.stock_out) as stock_out')
            ->groupBy(
                'products.id',
                'products.name',
                'products.sku',
                'products.sell_price',
                'product_units.name'
            )
            ->orderBy('products.name');
    }

    private function transformStockCard($row): array
    {
        return [
            'product_name' => $row->product_name,
            'product_sku' => $row->product_sku,
            'product_unit_name' => $row->product_unit_name,
            'sell_price' => (int) $row->sell_price,
            'stock_in' => (int) $row->stock_in,
            'stock_out' => (int) $row->stock_out,
            'difference' => (int) $row->stock_in - (int) $row->stock_out,
        ];
    }

    private function stockMovementQuery(): Builder
    {
        $query = $this->baseQuery();
        $query->join('locations', 'locations.id', '=', 'product_stock_movements.location_id');

        $selectAll = request('select_all_location', '1') == '1';
        $locs = array_map('intval', (array) request('locs', []));
        $excludeLocs = array_map('intval', (array) request('exclude_locs', []));

        if ($selectAll && count($excludeLocs) > 0) {
            $query->whereNotIn('product_stock_movements.location_id', $excludeLocs);
        } elseif (! $selectAll && count($locs) > 0) {
            $query->whereIn('product_stock_movements.location_id', $locs);
        }

        $date = DB::raw('DATE(product_stock_movements.created_at)');

        return $query
            ->select([
                'products.id as product_id',
                'products.name as product_name',
                'products.sku as product_sku',
                'products.sell_price',
                'product_units.name as product_unit_name',
                'locations.name as location_name',
            ])
            ->selectRaw('DATE(product_stock_movements.created_at) as date')
            ->selectRaw('SUM(product_stock_movements.stock_in) as stock_in')
            ->selectRaw('SUM(product_stock_movements.stock_out) as stock_out')
            ->groupBy(
                'products.id',
                'products.name',
                'products.sku',
                'products.sell_price',
                'product_units.name',
                'locations.name',
                $date
            )
            ->orderBy('products.name')
            ->orderBy('locations.name')
            ->orderBy($date);
    }

    private function transformStockMovement($row): array
    {
        return [
            'product_name' => $row->product_name,
            'product_sku' => $row->product_sku,
            'product_unit_name' => $row->product_unit_name,
            'location_name' => Str::title(Str::lower($row->location_name)),
            'date' => $row->date,
            'sell_price' => (int) $row->sell_price,
            'stock_in' => (int) $row->stock_in,
            'stock_out' => (int) $row->stock_out,
        ];
    }

    /**
     * Query dasar dari ReportStockCardQuery / ReportStockMovementQuery (legacy),
     * dibatasi ke lokasi milik entity, rentang tanggal, dan pencarian produk.
     */
    private function baseQuery(): Builder
    {
        $startAt = request('start_at')
            ? Carbon::parse(request('start_at'))->startOfDay()
            : Carbon::now()->subDays(7)->startOfDay();

        $endAt = request('end_at')
            ? Carbon::parse(request('end_at'))->endOfDay()
            : Carbon::now()->endOfDay();

        $locationIds = auth()->user()
            ->entity
            ->locations()
            ->pluck('id')
            ->toArray();

        return ProductStockMovement::query()
            ->join('products', 'products.id', '=', 'product_stock_movements.product_id')
            ->join('product_units', 'product_units.id', '=', 'product_stock_movements.product_unit_id')
            ->whereNotNull('product_stock_movements.resource_id')
            ->whereIn('product_stock_movements.location_id', $locationIds)
            ->whereBetween('product_stock_movements.created_at', [$startAt, $endAt])
            ->when(request('search'), function (Builder $query, string $search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('products.name', 'like', "%{$search}%")
                        ->orWhere('products.sku', 'like', "%{$search}%")
                        ->orWhere('products.barcode', 'like', "%{$search}%");
                });
            });
    }
}
