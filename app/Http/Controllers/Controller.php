<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;

abstract class Controller
{
    /**
     * Route model binding tidak tahu soal entity, jadi data milik entity lain
     * dijawab 404 supaya keberadaannya pun tidak terbuka.
     */
    protected function ensureOwnedByEntity(Model $model): void
    {
        abort_unless(
            $model->getAttribute('entity_id') !== null
                && (int) $model->getAttribute('entity_id') === (int) auth()->user()?->entity?->id,
            404,
        );
    }
}
