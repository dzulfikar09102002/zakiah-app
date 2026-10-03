<?php

namespace App\Services;

use App\Enums\StatusEnum;
use App\Models\Tax;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaxService
{
    public function getTaxes(): LengthAwarePaginator
    {
        $search = request('search', '');
        $status = request('status') === StatusEnum::Archived->value
            ? StatusEnum::Archived
            : StatusEnum::Active;

        return Tax::query()
            ->where('entity_id', auth()->user()?->entity?->id)
            ->where('status', $status)
            ->whereLike('name', "%{$search}%")
            ->orderBy('name')
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function store(array $data): Tax
    {
        $user = auth()->user();

        $tax = new Tax([
            'name' => $data['name'],
            'rate' => $data['rate'],
            'status' => StatusEnum::Active,
        ]);
        $tax->entity_id = $user->entity?->id;
        $tax->created_by = $user->id;
        $tax->updated_by = $user->id;
        $tax->save();

        return $tax;
    }

    public function update(Tax $tax, array $data): bool
    {
        $tax->updated_by = auth()->id();

        return $tax->update([
            'name' => $data['name'],
            'rate' => $data['rate'],
        ]);
    }

    /**
     * Arsipkan pajak aktif atau aktifkan kembali pajak yang diarsipkan (sesuai backoffice lama).
     */
    public function toggleStatus(Tax $tax): bool
    {
        $tax->updated_by = auth()->id();

        return $tax->update([
            'status' => $tax->status === StatusEnum::Active ? StatusEnum::Archived : StatusEnum::Active,
        ]);
    }

    public function ensureOwnedByEntity(Tax $tax): void
    {
        abort_unless((int) $tax->entity_id === (int) auth()->user()?->entity?->id, 404);
    }
}
