<?php

namespace App\Services;

use App\Models\Entity;

class EntityService
{
    public function __construct(
        protected EntityBrandingService $branding
    ) {}

    public function current(): Entity
    {
        $entity = auth()->user()?->entity;

        abort_unless($entity instanceof Entity, 404);

        return $entity;
    }

    public function logoUrl(Entity $entity): ?string
    {
        return $this->branding->logoUrl($entity->name);
    }

    public function update(Entity $entity, array $data): bool
    {
        return $entity->update($data);
    }

    /**
     * Pilihan zona waktu Indonesia (WIB/WITA/WIT).
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function timezoneOptions(): array
    {
        return [
            ['value' => 'Asia/Jakarta', 'label' => 'WIB (Asia/Jakarta)'],
            ['value' => 'Asia/Makassar', 'label' => 'WITA (Asia/Makassar)'],
            ['value' => 'Asia/Jayapura', 'label' => 'WIT (Asia/Jayapura)'],
        ];
    }
}
