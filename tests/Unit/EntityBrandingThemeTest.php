<?php

use App\Services\EntityBrandingService;

it('memilih tema warna sesuai entity', function (?int $entityId, bool $guest, string $theme) {
    expect((new EntityBrandingService)->theme($entityId, $guest))->toBe($theme);
})->with([
    'secaca (id 1)' => [1, false, 'secaca'],
    'zakiah (id 3)' => [3, false, 'zakiah'],
    'entity lain memakai zakiah' => [2, false, 'zakiah'],
    'user tanpa entity memakai zakiah' => [null, false, 'zakiah'],
    'tamu memakai perpaduan' => [null, true, 'duo'],
]);
