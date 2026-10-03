<?php

namespace App\Services\Concerns;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductLocationStock;
use App\Models\ProductUnit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Bagian yang sama dari dokumen stok (opname, penyesuaian, pindah stok) di backend lama:
 * karyawan pelaksana, waktu lokal lokasi, dan snapshot data produk pada baris detail.
 */
trait HandlesStockDocuments
{
    protected function entityId(): ?int
    {
        return auth()->user()?->entity?->id;
    }

    /**
     * Dokumen stok mencatat karyawan (employee_requested_by/approved_by), bukan user.
     */
    protected function employeeId(): int
    {
        $employeeId = auth()->user()?->employee?->id;

        if (! $employeeId) {
            throw ValidationException::withMessages([
                'employee' => 'Akun anda belum terhubung dengan data karyawan.',
            ]);
        }

        return $employeeId;
    }

    /**
     * Waktu sekarang menurut zona waktu lokasi (kolom local_*_at).
     */
    protected function localNow(?int $locationId): Carbon
    {
        $timezone = $locationId ? Location::find($locationId)?->timezone : null;

        return Carbon::now($timezone ?: 'UTC');
    }

    protected function ensureStatus(Model $document, string $status, string $key): void
    {
        if ($document->status !== $status) {
            throw ValidationException::withMessages([
                $key => 'Status dokumen tidak valid untuk aksi ini.',
            ]);
        }
    }

    /**
     * Isi snapshot produk (nama, sku, kode, kategori, satuan) seperti backend lama.
     */
    protected function fillProductSnapshot(Model $detail, Product $product, ?ProductUnit $unit = null): void
    {
        $unit ??= $product->productUnit;

        $detail->product_id = $product->id;
        $detail->product_name = $product->name;
        $detail->product_sku = $product->sku ?? '';
        $detail->product_code = $product->barcode ?? $product->code ?? '';
        $detail->product_unit_id = $unit?->id ?? $product->product_unit_id;
        $detail->product_unit_name = $unit?->name ?? '';
    }

    /**
     * Stok tercatat sebuah produk di lokasi.
     */
    protected function recordedStock(int $productId, int $locationId): int
    {
        return (int) ProductLocationStock::query()
            ->where('product_id', $productId)
            ->where('location_id', $locationId)
            ->sum('stock');
    }

    /**
     * @return array<int, Product>
     */
    protected function productsById(array $ids): array
    {
        return Product::query()
            ->with(['productUnit:id,name', 'productCategory:id,name'])
            ->where('entity_id', $this->entityId())
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id')
            ->all();
    }
}
